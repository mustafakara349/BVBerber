<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\StockCount;
use App\Models\StockCountItem;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockCountController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $query = StockCount::with('creator')
            ->where('branch_id', $branchId)
            ->latest('count_date');

        $counts = $query->paginate(15);

        return view('stock_counts.index', compact('counts'));
    }

    public function create()
    {
        $branchId = session('active_branch_id', 1);
        $products = Product::forBranch($branchId)->active()->orderBy('name')->get();

        return view('stock_counts.create', compact('products'));
    }

    public function store(Request $request, StockService $stockService)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'count_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.counted_quantity' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $branchId, $stockService) {
            
            // Sayım Kaydı Oluşturma
            $stockCount = StockCount::create([
                'branch_id' => $branchId,
                'count_date' => $validated['count_date'],
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // Kalemleri ekleme ve farklılıkları düzeltme
            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                
                if ($product->branch_id !== $branchId) {
                    continue;
                }

                $systemQuantity = $product->stock_quantity;
                $countedQuantity = $itemData['counted_quantity'];
                $difference = $countedQuantity - $systemQuantity;

                StockCountItem::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $product->id,
                    'system_quantity' => $systemQuantity,
                    'counted_quantity' => $countedQuantity,
                    'difference' => $difference,
                ]);

                // Eğer fark varsa, StockService üzerinden düzeltme yap
                if ($difference != 0) {
                    $notePrefix = $difference > 0 ? 'Sayım Fazlası: ' : 'Sayım Eksiği: ';
                    $stockService->createAdjustment(
                        $product, 
                        $countedQuantity, 
                        $notePrefix . ($validated['notes'] ?? 'Periyodik Sayım'), 
                        $stockCount->id
                    );
                }
            }
        });

        return redirect()->route('stock-counts.index')->with('success', 'Stok sayımı başarıyla kaydedildi ve farklılıklar stoklara yansıtıldı.');
    }

    public function show(StockCount $stockCount)
    {
        if ($stockCount->branch_id !== (int) session('active_branch_id', 1)) {
            abort(403);
        }

        $stockCount->load(['creator', 'items.product']);

        return view('stock_counts.show', compact('stockCount'));
    }
}
