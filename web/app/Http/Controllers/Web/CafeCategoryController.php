<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CafeCategory;
use Illuminate\Http\Request;

class CafeCategoryController extends Controller
{
    public function index()
    {
        $branchId   = session('active_branch_id', 1);
        $categories = CafeCategory::forBranch($branchId)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('cafe_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('cafe_categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $branchId = session('active_branch_id', 1);

        $category = CafeCategory::create([
            'branch_id'  => $branchId,
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? 0,
            'is_active'  => (bool) $request->input('is_active', true),
        ]);

        if ($request->wantsJson()) {
            return response()->json($category, 201);
        }

        return redirect()->route('cafe-categories.index')->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    public function edit(CafeCategory $cafeCategory)
    {
        return view('cafe_categories.edit', compact('cafeCategory'));
    }

    public function update(Request $request, CafeCategory $cafeCategory)
    {
        $branchId = session('active_branch_id', 1);

        if ($cafeCategory->branch_id !== $branchId) {
            abort(403);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $cafeCategory->update([
            'name'       => $request->name,
            'sort_order' => $request->sort_order ?? $cafeCategory->sort_order,
            'is_active'  => (bool) $request->input('is_active', $cafeCategory->is_active),
        ]);

        if ($request->wantsJson()) {
            return response()->json($cafeCategory->fresh());
        }

        return redirect()->route('cafe-categories.index')->with('success', 'Kategori güncellendi.');
    }

    public function destroy(Request $request, CafeCategory $cafeCategory)
    {
        $branchId = session('active_branch_id', 1);

        if ($cafeCategory->branch_id !== $branchId) {
            abort(403);
        }

        $cafeCategory->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('cafe-categories.index')->with('success', 'Kategori silindi.');
    }
}
