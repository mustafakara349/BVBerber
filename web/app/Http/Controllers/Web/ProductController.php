<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // -------------------------------------------------------------------------
    // Ürün kategorileri (sabit liste)
    // -------------------------------------------------------------------------
    public const CATEGORIES = [
        'bakim_urunu'   => '💆 Bakım Ürünü',
        'sac_urunu'     => '💈 Saç Ürünü',
        'kozmetik'      => '🧴 Kozmetik',
        'temizlik'      => '🧼 Temizlik',
        'berber_malzeme' => '✂️ Berber Malzemesi',
        'diger'         => '📦 Diğer',
    ];

    // -------------------------------------------------------------------------
    // Listeleme
    // -------------------------------------------------------------------------

    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = Product::forBranch($branchId)->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('product_category_id', $request->category);
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'active'       => $query->where('is_active', true),
                'inactive'     => $query->where('is_active', false),
                'out_of_stock' => $query->where('stock_quantity', '<=', 0),
                'low_stock'    => $query->whereRaw('stock_quantity > 0 AND stock_quantity <= critical_stock'),
                default        => null,
            };
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = \App\Models\ProductCategory::forBranch($branchId)->orderBy('sort_order')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Oluşturma formu
    // -------------------------------------------------------------------------

    public function create()
    {
        $branchId = session('active_branch_id', 1);
        $categories = \App\Models\ProductCategory::forBranch($branchId)->orderBy('sort_order')->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    // -------------------------------------------------------------------------
    // Kaydetme
    // -------------------------------------------------------------------------

    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'purchase_price'      => 'required|numeric|min:0',
            'sell_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'critical_stock'      => 'nullable|integer|min:0',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required'           => 'Ürün adı zorunludur.',
            'purchase_price.required' => 'Alış fiyatı zorunludur.',
            'sell_price.required'     => 'Satış fiyatı zorunludur.',
            'stock_quantity.required' => 'Stok miktarı zorunludur.',
            'image.image'             => 'Sadece görsel dosyası yükleyebilirsiniz.',
            'image.max'               => 'Görsel dosyası en fazla 2MB olabilir.',
        ]);

        $validated['branch_id'] = $branchId;
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'product' => $product,
                'message' => '✅ Ürün başarıyla eklendi.'
            ]);
        }

        return redirect()->route('products.index')->with('success', '✅ Ürün başarıyla eklendi.');
    }

    // -------------------------------------------------------------------------
    // Detay
    // -------------------------------------------------------------------------

    public function show(Product $product)
    {
        if ($product->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }
        $branchId = session('active_branch_id', 1);
        $categories = \App\Models\ProductCategory::forBranch($branchId)->orderBy('sort_order')->orderBy('name')->get();
        return view('products.show', compact('product', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Düzenleme formu
    // -------------------------------------------------------------------------

    public function edit(Product $product)
    {
        if ($product->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }
        $branchId = session('active_branch_id', 1);
        $categories = \App\Models\ProductCategory::forBranch($branchId)->orderBy('sort_order')->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Güncelleme
    // -------------------------------------------------------------------------

    public function update(Request $request, Product $product)
    {
        if ($product->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'sku'                 => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'description'         => 'nullable|string',
            'purchase_price'      => 'required|numeric|min:0',
            'sell_price'          => 'required|numeric|min:0',
            'stock_quantity'      => 'required|integer|min:0',
            'critical_stock'      => 'nullable|integer|min:0',
            'image'               => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'name.required'           => 'Ürün adı zorunludur.',
            'purchase_price.required' => 'Alış fiyatı zorunludur.',
            'sell_price.required'     => 'Satış fiyatı zorunludur.',
            'stock_quantity.required' => 'Stok miktarı zorunludur.',
            'image.image'             => 'Sadece görsel dosyası yükleyebilirsiniz.',
            'image.max'               => 'Görsel dosyası en fazla 2MB olabilir.',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', '✅ Ürün başarıyla güncellendi.');
    }

    // -------------------------------------------------------------------------
    // Silme
    // -------------------------------------------------------------------------

    public function destroy(Product $product)
    {
        if ($product->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', '🗑️ Ürün başarıyla silindi.');
    }

    // -------------------------------------------------------------------------
    // Durum Toggle (AJAX)
    // -------------------------------------------------------------------------

    public function toggleStatus(Product $product)
    {
        if ($product->branch_id !== (int) session('active_branch_id', 1)) {
            return response()->json(['success' => false], 403);
        }

        $product->update(['is_active' => !$product->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $product->is_active,
        ]);
    }
}
