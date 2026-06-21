<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Stok Girişi (Mal Alımı)
     */
    public function stockIn(Product $product, int $quantity, string $type = 'purchase', ?float $unitCost = null, ?string $refType = null, ?int $refId = null, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($product, $quantity, $type, $unitCost, $refType, $refId, $notes) {
            $beforeStock = $product->stock_quantity;
            $afterStock = $beforeStock + $quantity;

            // Stok Güncellemesi
            $product->stock_quantity = $afterStock;
            
            // Eğer purchase (satınalma) ise average_cost_price ve last_purchase_price güncelle
            if ($type === 'purchase' && $unitCost !== null) {
                $product->last_purchase_price = $unitCost;
                
                // Ortalama maliyet hesabı
                $currentTotalValue = $beforeStock * ($product->average_cost_price ?? $product->purchase_price ?? 0);
                $newTotalValue = $quantity * $unitCost;
                $product->average_cost_price = ($currentTotalValue + $newTotalValue) / $afterStock;
            }
            
            $product->save();

            // Hareket Kaydı
            return StockMovement::create([
                'branch_id' => $product->branch_id,
                'product_id' => $product->id,
                'movement_type' => $type,
                'quantity' => $quantity,
                'before_stock' => $beforeStock,
                'after_stock' => $afterStock,
                'unit_cost' => $unitCost,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Stok Çıkışı (Satış)
     */
    public function stockOut(Product $product, int $quantity, string $type = 'sale', ?float $unitCost = null, ?string $refType = null, ?int $refId = null, ?string $notes = null): StockMovement
    {
        if ($product->stock_quantity < $quantity) {
            throw new \Exception("Yetersiz stok! Mevcut stok: {$product->stock_quantity}");
        }

        return DB::transaction(function () use ($product, $quantity, $type, $unitCost, $refType, $refId, $notes) {
            $beforeStock = $product->stock_quantity;
            $afterStock = $beforeStock - $quantity;

            // Stok Güncellemesi
            $product->stock_quantity = $afterStock;
            $product->save();

            // Hareket Kaydı
            return StockMovement::create([
                'branch_id' => $product->branch_id,
                'product_id' => $product->id,
                'movement_type' => $type,
                'quantity' => -$quantity, // Negatif olarak kaydetmek daha analitik olabilir, ancak enum var, şimdilik pozitif tutalım ve tipi sale diyelim.
                'before_stock' => $beforeStock,
                'after_stock' => $afterStock,
                'unit_cost' => $unitCost,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Stok Sayım Farkı Düzeltmesi
     */
    public function createAdjustment(Product $product, int $newQuantity, ?string $notes = null, ?int $stockCountId = null): StockMovement
    {
        return DB::transaction(function () use ($product, $newQuantity, $notes, $stockCountId) {
            $difference = $newQuantity - $product->stock_quantity;

            if ($difference == 0) {
                throw new \Exception("Fark yok.");
            }

            if ($difference > 0) {
                return $this->stockIn($product, $difference, 'adjustment', null, 'stock_counts', $stockCountId, $notes);
            } else {
                return $this->stockOut($product, abs($difference), 'adjustment', null, 'stock_counts', $stockCountId, $notes);
            }
        });
    }

    /**
     * Fire / Hasar Kaydı
     */
    public function registerDamage(Product $product, int $quantity, ?string $notes = null): StockMovement
    {
        return $this->stockOut($product, $quantity, 'damage', null, null, null, $notes);
    }

    /**
     * Hizmet Tüketimi
     */
    public function registerConsumption(Product $product, int $quantity, int $appointmentId, ?string $notes = null): StockMovement
    {
        return $this->stockOut($product, $quantity, 'consumption', null, 'appointments', $appointmentId, $notes);
    }
}
