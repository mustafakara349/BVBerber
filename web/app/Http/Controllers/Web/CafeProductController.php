<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CafeProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CafeProductController extends Controller
{
    // -------------------------------------------------------------------------
    // (Categories are now managed dynamically via CafeCategory model)

    // -------------------------------------------------------------------------
    // Listeleme
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = CafeProduct::forBranch($branchId)->orderBy('sort_order')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'   => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'featured' => $query->where('is_featured', true),
                default    => null,
            };
        }

        $cafeProducts = $query->paginate(16)->withQueryString();
        $categories   = \App\Models\CafeCategory::forBranch($branchId)->orderBy('sort_order')->get();

        return view('cafe.index', compact('cafeProducts', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Kaydetme (store)
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'cafe_category_id' => 'nullable|exists:cafe_categories,id',
            'description' => 'nullable|string|max:1000',
            'ingredients' => 'nullable|string|max:1000',
            'price'       => 'required|numeric|min:0|max:99999',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ], [
            'name.required'  => 'Ürün adı zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
            'price.numeric'  => 'Fiyat geçerli bir sayı olmalıdır.',
            'image.image'    => 'Sadece görsel dosyası yükleyebilirsiniz.',
            'image.max'      => 'Görsel dosyası en fazla 2MB olabilir.',
        ]);

        $validated['branch_id'] = $branchId;
        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('cafe-products', 'public');
        }

        CafeProduct::create($validated);

        return redirect()->route('cafe.index')->with('success', '✅ Cafe ürünü başarıyla eklendi.');
    }

    // -------------------------------------------------------------------------
    // Güncelleme (update)
    // -------------------------------------------------------------------------

    public function update(Request $request, CafeProduct $cafeProduct)
    {
        $this->authorizeProduct($cafeProduct);

        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'cafe_category_id' => 'nullable|exists:cafe_categories,id',
            'description' => 'nullable|string|max:1000',
            'ingredients' => 'nullable|string|max:1000',
            'price'       => 'required|numeric|min:0|max:99999',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ], [
            'name.required'  => 'Ürün adı zorunludur.',
            'price.required' => 'Fiyat zorunludur.',
            'price.numeric'  => 'Fiyat geçerli bir sayı olmalıdır.',
            'image.image'    => 'Sadece görsel dosyası yükleyebilirsiniz.',
            'image.max'      => 'Görsel dosyası en fazla 2MB olabilir.',
        ]);

        $validated['is_active']   = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);

        if ($request->hasFile('image')) {
            // Eski görseli sil
            if ($cafeProduct->image_path) {
                Storage::disk('public')->delete($cafeProduct->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('cafe-products', 'public');
        }

        $cafeProduct->update($validated);

        return redirect()->route('cafe.index')->with('success', '✅ Cafe ürünü başarıyla güncellendi.');
    }

    // -------------------------------------------------------------------------
    // Silme (destroy)
    // -------------------------------------------------------------------------

    public function destroy(CafeProduct $cafeProduct)
    {
        $this->authorizeProduct($cafeProduct);

        if ($cafeProduct->image_path) {
            Storage::disk('public')->delete($cafeProduct->image_path);
        }

        $cafeProduct->delete();

        return redirect()->route('cafe.index')->with('success', '🗑️ Cafe ürünü silindi.');
    }

    // -------------------------------------------------------------------------
    // Durum Değiştirme (AJAX)
    // -------------------------------------------------------------------------

    public function toggleStatus(CafeProduct $cafeProduct)
    {
        $this->authorizeProduct($cafeProduct);

        $cafeProduct->update(['is_active' => !$cafeProduct->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $cafeProduct->is_active,
            'message'   => $cafeProduct->is_active ? 'Ürün aktifleştirildi.' : 'Ürün pasifleştirildi.',
        ]);
    }

    // -------------------------------------------------------------------------
    // Yardımcı: Yetki Kontrolü
    // -------------------------------------------------------------------------

    private function authorizeProduct(CafeProduct $cafeProduct): void
    {
        if ($cafeProduct->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403, 'Bu ürün için yetkiniz yok.');
        }
    }
}
