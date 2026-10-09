<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Models\ProductSale;
use App\Models\Product;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = StockMovement::with(['product', 'creator'])
            ->where('branch_id', $branchId)
            ->latest();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('movement_type', $request->type);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->paginate(20)->withQueryString();
        $products = Product::forBranch($branchId)->orderBy('name')->get();

        return view('stock_movements.index', compact('movements', 'products'));
    }

    public function show(StockMovement $stockMovement)
    {
        $branchId = session('active_branch_id', 1);

        if ($stockMovement->branch_id !== $branchId) {
            abort(403);
        }

        $stockMovement->load(['product.productCategory', 'creator']);

        // If this movement references a product sale, load it
        $referencedSale = null;
        if ($stockMovement->reference_type === 'product_sales' && $stockMovement->reference_id) {
            $referencedSale = ProductSale::with(['customer', 'seller', 'product'])
                ->find($stockMovement->reference_id);
        }

        if (request()->wantsJson()) {
            return response()->json([
                'movement'       => $stockMovement,
                'product'        => $stockMovement->product,
                'creator'        => $stockMovement->creator,
                'referenced_sale' => $referencedSale,
            ]);
        }

        return view('stock_movements.show', compact('stockMovement', 'referencedSale'));
    }
}
