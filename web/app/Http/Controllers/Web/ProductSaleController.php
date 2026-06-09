<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\Transaction;
use App\Models\User;
use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductSaleController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id', 1);

        $sales = ProductSale::forBranch($branchId)
            ->with(['product', 'customer', 'seller'])
            ->latest('sold_at')
            ->paginate(15);

        $products = Product::forBranch($branchId)->active()->where('stock_quantity', '>', 0)->get();
        $customers = User::customers()->active()->orderBy('first_name')->get();

        return view('products.sales', compact('sales', 'products', 'customers'));
    }

    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:users,id',
            'payment_method' => 'required|string|in:cash,credit_card,bank_transfer',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($product->branch_id !== $branchId) {
            abort(403);
        }

        if (! $product->isInStock($validated['quantity'])) {
            return back()->with('error', 'Yetersiz stok! Mevcut stok: ' . $product->stock_quantity);
        }

        DB::transaction(function () use ($validated, $product, $branchId) {
            $totalPrice = $product->sell_price * $validated['quantity'];

            // 1. Stok düşür
            $product->decrement('stock_quantity', $validated['quantity']);

            // 2. Satış kaydı
            $sale = ProductSale::create([
                'branch_id' => $branchId,
                'product_id' => $product->id,
                'customer_id' => $validated['customer_id'],
                'created_by' => Auth::id(),
                'quantity' => $validated['quantity'],
                'unit_price' => $product->sell_price,
                'total_price' => $totalPrice,
                'sold_at' => now(),
            ]);

            // 3. Kasaya Gelir olarak kaydet
            Transaction::create([
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
                'transaction_type' => TransactionType::Income,
                'amount' => $totalPrice,
                'currency' => 'TRY',
                'payment_method' => $validated['payment_method'],
                'description' => 'Ürün Satışı - ' . $product->name . ' (' . $validated['quantity'] . ' adet)',
                'transaction_date' => now(),
            ]);
            
            // Invalidate dashboard cache
            app(\App\Services\DashboardService::class)->flushBranchCache($branchId);
        });

        return redirect()->route('products.sales.index')->with('success', 'Ürün satışı başarıyla gerçekleştirildi.');
    }
}
