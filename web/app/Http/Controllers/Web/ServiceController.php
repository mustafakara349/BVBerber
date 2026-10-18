<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);
        $query = Service::where('branch_id', $branchId)->with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $services = $query->paginate(15)->withQueryString();
        $categories = ServiceCategory::all();
        return view('services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $categories = ServiceCategory::all();
        return view('services.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:service_categories,id',
            'duration_minutes' => 'required|integer|min:5',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'gender_type' => 'required|in:male,female,unisex',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
        ], [
            'image.image' => 'Yüklenen dosya geçerli bir raster görsel olmalıdır.',
            'image.mimes' => 'Yalnızca JPEG, PNG veya WebP formatında görsel yüklenebilir. SVG veya GIF kabul edilmez.',
            'image.mimetypes' => 'Yalnızca geçerli JPEG, PNG veya WebP görseli yüklenebilir.',
            'image.max' => 'Görsel boyutu en fazla 2MB olabilir.',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['branch_id'] = session('active_branch_id', 1);
        $validated['is_active'] = true;
        
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_featured'] = $request->has('is_featured');

        if ($request->hasFile('image')) {
            $validated['image'] = $this->validateAndStoreImage($request);
        }

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Hizmet başarıyla eklendi.');
    }

    public function edit(Service $service)
    {
        if ($service->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $categories = ServiceCategory::all();
        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        if ($service->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'category_id' => 'nullable|exists:service_categories,id',
            'duration_minutes' => 'required|integer|min:5',
            'price' => 'required|numeric|min:0',
            'discounted_price' => 'nullable|numeric|min:0',
            'gender_type' => 'required|in:male,female,unisex',
            'description' => 'nullable|string',
            'is_popular' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
        ], [
            'image.image' => 'Yüklenen dosya geçerli bir raster görsel olmalıdır.',
            'image.mimes' => 'Yalnızca JPEG, PNG veya WebP formatında görsel yüklenebilir. SVG veya GIF kabul edilmez.',
            'image.mimetypes' => 'Yalnızca geçerli JPEG, PNG veya WebP görseli yüklenebilir.',
            'image.max' => 'Görsel boyutu en fazla 2MB olabilir.',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        $validated['is_popular'] = $request->has('is_popular');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $this->validateAndStoreImage($request, $service->image);
        }

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Hizmet başarıyla güncellendi.');
    }

    /**
     * Deep server-side content verification and safe storage for service images.
     */
    protected function validateAndStoreImage(Request $request, ?string $oldPath = null): ?string
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $realPath = $file->getRealPath();

        // 1. Inspect actual binary image headers (rejects SVG, HTML, scripts)
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages([
                'image' => ['Yüklenen dosya geçerli bir raster görsel (JPEG, PNG veya WebP) formatında olmalıdır. SVG veya GIF kabul edilmez.'],
            ]);
        }

        // 2. Magic bytes verification via finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($realPath);
        if (!in_array($detectedMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages([
                'image' => ['Geçersiz görsel türü tespit edildi.'],
            ]);
        }

        // 3. Derive safe extension strictly from inspected binary type
        $safeExt = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };

        // 4. Safe random UUID filename (never trust client filename)
        $safeFilename = Str::uuid()->toString() . '.' . $safeExt;
        $storedPath = $file->storeAs('services', $safeFilename, 'public');

        // 5. Clean up old image only after successful verification and storage
        if ($oldPath && $oldPath !== $storedPath && str_starts_with($oldPath, 'services/') && !str_contains($oldPath, '..')) {
            Storage::disk('public')->delete($oldPath);
        }

        return $storedPath;
    }

    public function destroy(Service $service)
    {
        if ($service->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $service->delete();
        return redirect()->route('services.index')->with('success', 'Hizmet silindi.');
    }

    public function toggleStatus(Service $service)
    {
        if ($service->branch_id !== session('active_branch_id', 1)) {
            return response()->json(['success' => false, 'message' => 'Yetkisiz işlem.'], 403);
        }

        $service->update(['is_active' => !$service->is_active]);
        return response()->json(['success' => true, 'is_active' => $service->is_active]);
    }
}
