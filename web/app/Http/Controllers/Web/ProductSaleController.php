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
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = ProductSale::forBranch($branchId)
            ->selectRaw('
                IFNULL(sale_code, id) as group_id,
                MAX(sale_code) as sale_code,
                MAX(id) as id,
                MAX(sold_at) as sold_at,
                MAX(customer_id) as customer_id,
                MAX(created_by) as created_by,
                SUM(quantity) as total_quantity,
                SUM(total_price) as total_price,
                MAX(payment_method) as payment_method,
                COUNT(id) as product_count
            ')
            ->groupByRaw('IFNULL(sale_code, id)');

        // Filtreleme
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('sold_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('sold_at', '<=', $request->date_to);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderBy('sold_at', 'desc')->paginate(15)->withQueryString();

        // Get product details for the grouped items
        $groupKeys = $sales->pluck('group_id');
        $saleItems = ProductSale::where(function($q) use ($groupKeys) {
                $q->whereIn('sale_code', $groupKeys)->orWhereIn('id', $groupKeys);
            })
            ->with(['product', 'seller', 'customer'])
            ->get();

        foreach($sales as $sale) {
            // find items belonging to this group
            $items = $saleItems->filter(function($i) use ($sale) {
                return ($i->sale_code === $sale->group_id) || ($i->id == $sale->group_id);
            });
            
            // Assign a random item's customer and seller to the group since they are all the same
            $sale->customer = $items->first()->customer ?? null;
            $sale->seller = $items->first()->seller ?? null;

            if ($items->count() == 1) {
                $sale->display_product_name = $items->first()->product->name ?? 'Silinmiş Ürün';
                $sale->display_unit_price = $items->first()->unit_price;
            } else {
                $sale->display_product_name = $items->count() . ' Çeşit Ürün';
                $sale->display_unit_price = null; // Mixed prices
            }
        }

        $products = Product::forBranch($branchId)->active()->orderBy('name')->get();
        $customers = User::customers()->active()->orderBy('first_name')->get();

        return view('products.sales', compact('sales', 'products', 'customers'));
    }

    /**
     * Ajax endpoint to get sale details
     */
    public function show($identifier)
    {
        $branchId = session('active_branch_id', 1);

        // Fetch all items belonging to this sale code or id
        $saleItems = ProductSale::where('branch_id', $branchId)
            ->where(function($q) use ($identifier) {
                $q->where('sale_code', $identifier)->orWhere('id', $identifier);
            })
            ->with(['product.productCategory', 'customer', 'seller'])
            ->get();

        if ($saleItems->isEmpty()) {
            abort(404);
        }

        $productSale = $saleItems->first();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'sale' => $productSale, // returning the first item representing the group
                'items' => $saleItems,
                'product' => $productSale->product,
                'customer' => $productSale->customer,
                'seller' => $productSale->seller,
            ]);
        }

        return view('products.sale_detail', compact('productSale', 'saleItems'));
    }

    /**
     * Store multi-item cart sale
     */
    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1|max:9999',
            'customer_id'        => 'nullable|exists:users,id',
            'payment_method'     => 'required|string|in:cash,credit_card,bank_transfer',
        ]);

        // Validate each product belongs to this branch & has enough stock
        $productMap = [];
        foreach ($validated['items'] as $index => $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('branch_id', $branchId)
                ->where('is_active', true)
                ->first();

            if (!$product) {
                return back()->with('error', "Ürün bulunamadı veya bu şubeye ait değil. (Satır " . ($index + 1) . ")");
            }

            if (!$product->isInStock($item['quantity'])) {
                return back()->with('error', "'{$product->name}' ürünü için yetersiz stok! Mevcut: {$product->stock_quantity}");
            }

            $productMap[$item['product_id']] = $product;
        }

        // Deduplicate: if same product appears multiple times, merge quantities
        $mergedItems = [];
        foreach ($validated['items'] as $item) {
            $pid = $item['product_id'];
            if (isset($mergedItems[$pid])) {
                $mergedItems[$pid]['quantity'] += $item['quantity'];
            } else {
                $mergedItems[$pid] = $item;
            }
        }

        // Re-validate merged stock
        foreach ($mergedItems as $pid => $item) {
            $product = $productMap[$pid];
            if (!$product->isInStock($item['quantity'])) {
                return back()->with('error', "'{$product->name}' ürünü için yetersiz stok! Mevcut: {$product->stock_quantity}, istenen: {$item['quantity']}");
            }
        }

        $saleGroupCode = 'SALE-' . time() . '-' . rand(100, 999);

        DB::transaction(function () use ($mergedItems, $validated, $productMap, $branchId, $saleGroupCode) {
            $grandTotal = 0;
            $saleDescriptions = [];

            foreach ($mergedItems as $pid => $item) {
                $product = $productMap[$pid];
                $totalPrice = $product->sell_price * $item['quantity'];
                $grandTotal += $totalPrice;

                // 1. Satış kaydı
                $sale = ProductSale::create([
                    'branch_id'      => $branchId,
                    'product_id'     => $product->id,
                    'customer_id'    => $validated['customer_id'] ?? null,
                    'created_by'     => Auth::id(),
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $product->sell_price,
                    'total_price'    => $totalPrice,
                    'sold_at'        => now(),
                    'sale_code'      => $saleGroupCode,
                    'payment_method' => $validated['payment_method'],
                    'discount_amount' => 0,
                ]);

                // 2. Stok düşür
                app(\App\Services\StockService::class)->stockOut(
                    $product,
                    $item['quantity'],
                    'sale',
                    $product->sell_price,
                    'product_sales',
                    $sale->id,
                    'Satış İşlemi'
                );

                $saleDescriptions[] = $product->name . ' (' . $item['quantity'] . ' adet)';
            }

            // 3. Kasaya tek gelir kaydı
            Transaction::create([
                'branch_id'        => $branchId,
                'created_by'       => Auth::id(),
                'transaction_type' => TransactionType::Income,
                'amount'           => $grandTotal,
                'currency'         => 'TRY',
                'payment_method'   => $validated['payment_method'],
                'description'      => 'Ürün Satışı [' . $saleGroupCode . '] - ' . implode(', ', $saleDescriptions),
                'transaction_date' => now(),
            ]);

            // 4. Cache temizle
            app(\App\Services\DashboardService::class)->flushBranchCache($branchId);
        });

        return redirect()->route('products.sales.index')->with('success', 'Satış başarıyla tamamlandı!');
    }
}
