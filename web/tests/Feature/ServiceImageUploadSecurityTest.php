<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceImageUploadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $manager;
    private Branch $branch;
    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        Storage::fake('public');

        $managerRole = Role::firstOrCreate(['slug' => 'manager'], ['name' => 'manager', 'label' => 'Yönetici']);
        $suffix = Str::lower(Str::random(6));

        $this->branch = Branch::firstOrCreate(
            ['slug' => 'service-test-branch'],
            [
                'name' => 'Service Test Branch',
                'email' => "service_{$suffix}@test.com",
                'phone' => '055' . rand(1000000, 9999999),
                'city' => 'Istanbul',
                'district' => 'Sisli',
                'address' => 'Test Address',
                'is_active' => true,
            ]
        );

        $this->manager = User::create([
            'uuid' => (string) Str::uuid(),
            'first_name' => 'Manager',
            'last_name' => 'Service',
            'email' => "mgr_srv_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'role_id' => $managerRole->id,
            'status' => 'active',
        ]);

        $this->category = ServiceCategory::firstOrCreate(
            ['name' => 'Genel'],
            ['slug' => 'genel-' . $suffix, 'branch_id' => $this->branch->id]
        );
    }

    /**
     * Test valid JPEG, PNG, and WebP service image uploads are accepted.
     */
    public function test_valid_raster_images_are_accepted(): void
    {
        // 1. JPEG
        $jpegFile = UploadedFile::fake()->image('haircut.jpg', 400, 400);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => 'Saç Kesimi JPEG ' . Str::random(4),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 250,
                'gender_type' => 'male',
                'image' => $jpegFile,
            ]);

        $response->assertRedirect(route('services.index'));
        $response->assertSessionHasNoErrors();

        $service = Service::latest('id')->first();
        $this->assertNotNull($service->image);
        $this->assertStringStartsWith('services/', $service->image);
        $this->assertStringEndsWith('.jpg', $service->image);
        $filename = basename($service->image);
        $this->assertNotEquals('haircut.jpg', $filename);
        $this->assertTrue(Str::isUuid(pathinfo($filename, PATHINFO_FILENAME)));
        Storage::disk('public')->assertExists($service->image);

        // 2. PNG
        $pngFile = UploadedFile::fake()->image('beard.png', 400, 400);

        $responsePng = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => 'Sakal Kesimi PNG ' . Str::random(4),
                'category_id' => $this->category->id,
                'duration_minutes' => 20,
                'price' => 150,
                'gender_type' => 'male',
                'image' => $pngFile,
            ]);

        $responsePng->assertRedirect(route('services.index'));
        $servicePng = Service::latest('id')->first();
        $this->assertStringEndsWith('.png', $servicePng->image);
        Storage::disk('public')->assertExists($servicePng->image);
    }

    /**
     * Test SVG files are strictly rejected from service uploads.
     */
    public function test_svg_upload_is_strictly_rejected(): void
    {
        $svgContent = '<?xml version="1.0" standalone="no"?><svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $svgFile = UploadedFile::fake()->createWithContent('malicious.svg', $svgContent);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => 'Hizmet SVG ' . Str::random(4),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 200,
                'gender_type' => 'unisex',
                'image' => $svgFile,
            ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('services', ['name' => 'Hizmet SVG']);
    }

    /**
     * Test GIF files are rejected from service uploads.
     */
    public function test_gif_upload_is_rejected(): void
    {
        // 1x1 GIF magic bytes
        $gifBytes = "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\xff\xff\xff\x00\x00\x00\x21\xf9\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02\x44\x01\x00\x3b";
        $gifFile = UploadedFile::fake()->createWithContent('animation.gif', $gifBytes);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => 'Hizmet GIF ' . Str::random(4),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 200,
                'gender_type' => 'male',
                'image' => $gifFile,
            ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * Test misleading extension (SVG disguised as .jpg) is rejected by deep verification.
     */
    public function test_disguised_svg_with_jpg_extension_is_rejected(): void
    {
        $fakeJpgContent = '<svg xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="40"/><script>alert(1)</script></svg>';
        $fakeFile = UploadedFile::fake()->createWithContent('fake.jpg', $fakeJpgContent);

        $response = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => 'Fake Image Service ' . Str::random(4),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 200,
                'gender_type' => 'male',
                'image' => $fakeFile,
            ]);

        $response->assertSessionHasErrors('image');
    }

    /**
     * Test updating service with valid image replaces old file; updating with invalid image retains original.
     */
    public function test_service_update_preserves_old_image_on_invalid_upload(): void
    {
        $originalFile = UploadedFile::fake()->image('original.jpg', 300, 300);
        $origName = 'Test Service Orig ' . Str::random(8);

        $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->post(route('services.store'), [
                'name' => $origName,
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 200,
                'gender_type' => 'male',
                'image' => $originalFile,
            ]);

        $service = Service::where('name', $origName)->firstOrFail();
        $originalImagePath = $service->image;
        Storage::disk('public')->assertExists($originalImagePath);

        // Attempt update with invalid SVG
        $svgFile = UploadedFile::fake()->createWithContent('exploit.svg', '<svg></svg>');
        $updateResponse = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->put(route('services.update', $service), [
                'name' => 'Test Service Upd ' . Str::random(8),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 200,
                'gender_type' => 'male',
                'image' => $svgFile,
            ]);

        $updateResponse->assertSessionHasErrors('image');

        // Original image must STILL exist on disk and in database
        $service->refresh();
        $this->assertEquals($originalImagePath, $service->image);
        Storage::disk('public')->assertExists($originalImagePath);

        // Now update with a valid PNG image
        $newValidPng = UploadedFile::fake()->image('new_valid.png', 400, 400);
        $validUpdateResponse = $this->actingAs($this->manager)
            ->withSession(['active_branch_id' => $this->branch->id])
            ->put(route('services.update', $service), [
                'name' => 'Test Service Valid ' . Str::random(8),
                'category_id' => $this->category->id,
                'duration_minutes' => 30,
                'price' => 220,
                'gender_type' => 'male',
                'image' => $newValidPng,
            ]);

        $validUpdateResponse->assertRedirect(route('services.index'));
        $service->refresh();

        // New image exists and old image was safely deleted
        $this->assertNotEquals($originalImagePath, $service->image);
        $this->assertStringEndsWith('.png', $service->image);
        Storage::disk('public')->assertExists($service->image);
        Storage::disk('public')->assertMissing($originalImagePath);
    }
}
