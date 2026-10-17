<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfilePhotoUploadSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $customerUser;
    private User $secondUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        Storage::fake('public');

        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $suffix = Str::lower(Str::random(6));

        $this->customerUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $customerRole->id,
            'first_name' => 'Photo',
            'last_name' => 'Tester',
            'email' => "phototester_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $this->secondUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $customerRole->id,
            'first_name' => 'Second',
            'last_name' => 'User',
            'email' => "second_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'status' => 'active',
            'profile_photo' => 'profile_photos/original_second_user.jpg',
        ]);
    }

    public function test_unauthenticated_request_is_rejected_with_401(): void
    {
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $file,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_upload_valid_jpeg_and_png_images(): void
    {
        Sanctum::actingAs($this->customerUser);

        // 1. Valid JPEG
        $jpegFile = UploadedFile::fake()->image('avatar.jpg', 300, 300);
        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $jpegFile,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->customerUser->refresh();
        $storedPath = $this->customerUser->profile_photo;
        $this->assertNotNull($storedPath);
        $this->assertStringStartsWith('profile_photos/', $storedPath);
        $this->assertStringEndsWith('.jpg', $storedPath);

        // Verify stored file has a safe UUID filename (not original 'avatar.jpg')
        $filename = basename($storedPath);
        $this->assertNotEquals('avatar.jpg', $filename);
        $this->assertTrue(Str::isUuid(pathinfo($filename, PATHINFO_FILENAME)));
        Storage::disk('public')->assertExists($storedPath);

        // 2. Valid PNG
        $pngFile = UploadedFile::fake()->image('photo.png', 400, 400);
        $pngResponse = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $pngFile,
        ]);

        $pngResponse->assertStatus(200);
        $this->customerUser->refresh();
        $newStoredPath = $this->customerUser->profile_photo;
        $this->assertStringEndsWith('.png', $newStoredPath);
        Storage::disk('public')->assertExists($newStoredPath);
    }

    public function test_upload_rejects_svg_file(): void
    {
        Sanctum::actingAs($this->customerUser);

        $svgContent = '<?xml version="1.0" standalone="no"?><svg xmlns="http://www.w3.org/2000/svg"><script>alert("XSS")</script></svg>';
        $svgFile = UploadedFile::fake()->createWithContent('malicious.svg', $svgContent);

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $svgFile,
        ]);

        $response->assertStatus(422);
        $this->customerUser->refresh();
        $this->assertNull($this->customerUser->profile_photo);
    }

    public function test_upload_rejects_spoofed_mime_and_extension_with_executable_content(): void
    {
        Sanctum::actingAs($this->customerUser);

        // A PHP web shell disguised with .png extension and fake image/png mime
        $fakePhpScript = "<?php echo 'malicious backdoor'; phpinfo(); ?>";
        $spoofedFile = UploadedFile::fake()->createWithContent(
            'shell.png',
            $fakePhpScript
        );

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $spoofedFile,
        ]);

        $response->assertStatus(422);
        $this->customerUser->refresh();
        $this->assertNull($this->customerUser->profile_photo);
    }

    public function test_upload_rejects_oversized_image_exceeding_two_megabytes(): void
    {
        Sanctum::actingAs($this->customerUser);

        // 2.5MB (2560 KB) image exceeds the 2048 KB limit
        $oversizedFile = UploadedFile::fake()->image('huge.jpg')->size(2560);

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $oversizedFile,
        ]);

        $response->assertStatus(422);
        $this->customerUser->refresh();
        $this->assertNull($this->customerUser->profile_photo);
    }

    public function test_upload_rejects_request_without_photo_file(): void
    {
        Sanctum::actingAs($this->customerUser);

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', []);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Photo file required']);
    }

    public function test_replacing_photo_deletes_previous_photo_from_disk(): void
    {
        Sanctum::actingAs($this->customerUser);

        // Upload first photo
        $file1 = UploadedFile::fake()->image('first.jpg', 200, 200);
        $response1 = $this->postJson('/api/v1/mobile/me/upload-photo', ['photo' => $file1]);
        $response1->assertStatus(200);

        $this->customerUser->refresh();
        $path1 = $this->customerUser->profile_photo;
        Storage::disk('public')->assertExists($path1);

        // Upload second photo to replace the first
        $file2 = UploadedFile::fake()->image('second.png', 250, 250);
        $response2 = $this->postJson('/api/v1/mobile/me/upload-photo', ['photo' => $file2]);
        $response2->assertStatus(200);

        $this->customerUser->refresh();
        $path2 = $this->customerUser->profile_photo;

        // Path 1 should now be deleted, Path 2 must exist
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertExists($path2);
        $this->assertNotEquals($path1, $path2);
    }

    public function test_authenticated_user_can_upload_valid_webp_image(): void
    {
        Sanctum::actingAs($this->customerUser);

        $webpFile = UploadedFile::fake()->image('avatar.webp', 250, 250);
        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $webpFile,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->customerUser->refresh();
        $storedPath = $this->customerUser->profile_photo;
        $this->assertNotNull($storedPath);
        $this->assertStringStartsWith('profile_photos/', $storedPath);
        $this->assertStringEndsWith('.webp', $storedPath);

        $filename = basename($storedPath);
        $this->assertNotEquals('avatar.webp', $filename);
        $this->assertTrue(Str::isUuid(pathinfo($filename, PATHINFO_FILENAME)));
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_upload_rejects_corrupted_or_invalid_image_content(): void
    {
        Sanctum::actingAs($this->customerUser);

        // File claims to be .jpg with image/jpeg mime, but content is corrupt non-image bytes
        $corruptFile = UploadedFile::fake()->createWithContent(
            'corrupt.jpg',
            "NOT_A_VALID_IMAGE_HEADER_RANDOM_CORRUPT_PAYLOAD_" . str_repeat('X', 512)
        );

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $corruptFile,
        ]);

        $response->assertStatus(422);
        $this->customerUser->refresh();
        $this->assertNull($this->customerUser->profile_photo);
    }

    public function test_failed_upload_does_not_delete_existing_valid_photo_or_leave_orphaned_files(): void
    {
        Sanctum::actingAs($this->customerUser);

        // Put an existing valid photo on disk
        $existingPath = 'profile_photos/valid_existing_user_photo.jpg';
        Storage::disk('public')->put($existingPath, 'fake_valid_photo_content');
        $this->customerUser->update(['profile_photo' => $existingPath]);

        $filesBefore = Storage::disk('public')->allFiles('profile_photos');
        $this->assertContains($existingPath, $filesBefore);

        // Attempt to upload an invalid file (corrupt content)
        $invalidFile = UploadedFile::fake()->createWithContent(
            'corrupt.jpg',
            "INVALID_CORRUPT_DATA"
        );

        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $invalidFile,
        ]);

        $response->assertStatus(422);

        // Verify existing photo was NOT deleted and user's profile_photo was NOT changed
        $this->customerUser->refresh();
        $this->assertEquals($existingPath, $this->customerUser->profile_photo);
        Storage::disk('public')->assertExists($existingPath);

        // Verify no orphaned files were created in storage
        $filesAfter = Storage::disk('public')->allFiles('profile_photos');
        $this->assertEquals($filesBefore, $filesAfter);
    }

    public function test_old_file_deletion_cannot_escape_profile_photos_directory_path_traversal(): void
    {
        Sanctum::actingAs($this->customerUser);

        // Place a sensitive file outside profile_photos in public storage
        Storage::disk('public')->put('system_config.json', '{"secret": "do_not_delete"}');
        Storage::disk('public')->assertExists('system_config.json');

        // Set user's profile photo to a path traversal attempt
        $this->customerUser->update(['profile_photo' => 'profile_photos/../system_config.json']);

        // Upload a valid new photo
        $validFile = UploadedFile::fake()->image('new.jpg', 200, 200);
        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $validFile,
        ]);

        $response->assertStatus(200);

        // Verify system_config.json was NOT deleted because path traversal is blocked
        Storage::disk('public')->assertExists('system_config.json');

        $this->customerUser->refresh();
        $this->assertNotEquals('profile_photos/../system_config.json', $this->customerUser->profile_photo);
        $this->assertStringStartsWith('profile_photos/', $this->customerUser->profile_photo);
    }

    public function test_user_cannot_overwrite_another_users_profile_photo(): void
    {
        Sanctum::actingAs($this->customerUser);

        $initialSecondUserPhoto = $this->secondUser->profile_photo;

        // Try to supply user_id pointing to another user
        $file = UploadedFile::fake()->image('avatar.jpg', 200, 200);
        $response = $this->postJson('/api/v1/mobile/me/upload-photo', [
            'photo' => $file,
            'user_id' => $this->secondUser->id,
            'id' => $this->secondUser->id,
        ]);

        $response->assertStatus(200);

        // Second user must be completely unaffected
        $this->secondUser->refresh();
        $this->assertEquals($initialSecondUserPhoto, $this->secondUser->profile_photo);

        // Only the authenticated customer user was updated
        $this->customerUser->refresh();
        $this->assertNotNull($this->customerUser->profile_photo);
    }

    public function test_employee_controller_web_upload_enforces_raster_validation_and_safe_storage(): void
    {
        $adminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $adminUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $adminRole->id,
            'first_name' => 'Admin',
            'last_name' => 'Boss',
            'email' => 'superadmin_' . Str::random(6) . '@bvberber.com',
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $validPhoto = UploadedFile::fake()->image('staff.png', 300, 300);
        $suffix = Str::lower(Str::random(6));

        $response = $this->actingAs($adminUser)->withSession(['active_branch_id' => 1])->post('/employees', [
            'first_name' => 'John',
            'last_name' => 'Staff',
            'email' => "staff_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'role_id' => $adminRole->id,
            'password' => 'Password123!',
            'salary_type' => 'fixed',
            'salary_amount' => 30000,
            'commission_rate' => 10,
            'hire_date' => '2026-01-01',
            'profile_photo' => $validPhoto,
        ]);

        $response->assertRedirect('/employees');

        $createdUser = User::where('email', "staff_{$suffix}@bvberber.com")->first();
        $this->assertNotNull($createdUser);
        $this->assertNotNull($createdUser->profile_photo);
        $this->assertStringStartsWith('profile_photos/', $createdUser->profile_photo);
        $this->assertStringEndsWith('.png', $createdUser->profile_photo);

        $filename = basename($createdUser->profile_photo);
        $this->assertNotEquals('staff.png', $filename);
        $this->assertTrue(Str::isUuid(pathinfo($filename, PATHINFO_FILENAME)));
        Storage::disk('public')->assertExists($createdUser->profile_photo);
    }

    public function test_employee_controller_web_upload_rejects_corrupt_or_spoofed_files(): void
    {
        $adminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $adminUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $adminRole->id,
            'first_name' => 'Admin',
            'last_name' => 'Boss',
            'email' => 'superadmin_' . Str::random(6) . '@bvberber.com',
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        $corruptFile = UploadedFile::fake()->createWithContent(
            'backdoor.png',
            "<?php phpinfo(); ?> NOT_A_REAL_PNG"
        );
        $suffix = Str::lower(Str::random(6));

        $response = $this->actingAs($adminUser)->withSession(['active_branch_id' => 1])->post('/employees', [
            'first_name' => 'Malicious',
            'last_name' => 'Staff',
            'email' => "malicious_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'role_id' => $adminRole->id,
            'password' => 'Password123!',
            'salary_type' => 'fixed',
            'salary_amount' => 30000,
            'commission_rate' => 10,
            'hire_date' => '2026-01-01',
            'profile_photo' => $corruptFile,
        ]);

        $response->assertSessionHasErrors('profile_photo');
        $createdUser = User::where('email', "malicious_{$suffix}@bvberber.com")->first();
        $this->assertNull($createdUser);
    }
}
