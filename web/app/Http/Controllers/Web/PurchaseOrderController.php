<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = PurchaseOrder::with(['supplier', 'creator'])
            ->where('branch_id', $branchId)
            ->latest('purchase_date');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', "%{$request->invoice_number}%");
        }

        $orders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase_orders.index', compact('orders', 'suppliers'));
    }

    public function create()
    {
        $branchId = session('active_branch_id', 1);
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products = Product::forBranch($branchId)->active()->orderBy('name')->get();

        return view('purchase_orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request, StockService $stockService)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'invoice_number' => 'nullable|string|max:100',
            'purchase_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $branchId, $stockService) {
            $subtotal = 0;
            
            // Fatura Oluşturma
            $order = PurchaseOrder::create([
                'branch_id' => $branchId,
                'supplier_id' => $validated['supplier_id'],
                'invoice_number' => $validated['invoice_number'],
                'purchase_date' => $validated['purchase_date'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
                'subtotal' => 0, // Aşağıda hesaplanacak
                'total_amount' => 0,
            ]);

            // Kalemleri ekleme ve stok girişi
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                // Güvenlik: Ürün aynı şubede mi?
                if ($product->branch_id !== $branchId) {
                    continue;
                }

                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $totalPrice;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                ]);

                // Stok Servisi üzerinden stokları gir
                $stockService->stockIn(
                    $product,
                    $itemData['quantity'],
                    'purchase',
                    $itemData['unit_price'],
                    'purchase_orders',
                    $order->id,
                    'Mal Alımı: ' . ($validated['invoice_number'] ?? 'Faturasız')
                );
            }

            // Toplam tutarları güncelle
            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal, // Gelecekte vergi vb eklenebilir
            ]);
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Mal alım faturası başarıyla işlendi ve stoklara yansıtıldı.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }

        $purchaseOrder->load(['supplier', 'creator', 'items.product']);

        return view('purchase_orders.show', compact('purchaseOrder'));
    }
}
