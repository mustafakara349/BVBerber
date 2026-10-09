<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Ürünler sayfasında kategori listesi + modal için yönlendirir.
     * Ayrıca ProductController::index() içindeki $categories değişkenine veri sağlar.
     * Bu endpoint artık kullanılmasa da çakışma olmaması için kaldırılmıyor.
     */
    public function index()
    {
        $branchId   = session('active_branch_id', 1);
        $categories = ProductCategory::forBranch($branchId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('product_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('product_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $branchId = session('active_branch_id', 1);

        $category = ProductCategory::create([
            'branch_id'  => $branchId,
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => (bool) $request->input('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return redirect()->route('product-categories.index')->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product_categories.edit', compact('productCategory'));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $branchId = session('active_branch_id', 1);

        if ($productCategory->branch_id !== $branchId) {
            abort(403);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $productCategory->update([
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? $productCategory->sort_order,
            'is_active'  => (bool) $request->input('is_active', $productCategory->is_active),
        ]);

        if ($request->wantsJson()) {
            return response()->json($productCategory->fresh());
        }

        return redirect()->route('product-categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Request $request, ProductCategory $productCategory)
    {
        $branchId = session('active_branch_id', 1);

        if ($productCategory->branch_id !== $branchId) {
            abort(403);
        }

        $productCategory->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('product-categories.index')->with('success', 'Kategori silindi.');
    }
}
