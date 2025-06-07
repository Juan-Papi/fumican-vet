<?php

namespace App\Services\Sales;

use App\Models\Sales\Inventory;
use App\Models\Sales\StockMovement;
use App\Repositories\Sales\InventoryRepository;

class InventoryService
{
    public function __construct(protected InventoryRepository $inventoryRepository) {}

    public function getAllInventories()
    {
        return $this->inventoryRepository->getAll();
    }

    public function getinventoryById($id)
    {
        return $this->inventoryRepository->findById($id);
    }

    public function createInventory(array $userData)
    {
        return $this->inventoryRepository->create($userData);
    }

    public function getGroupedInventoriesByMedicament($warehouseId)
    {
        return $this->inventoryRepository->getGroupedInventoriesByMedicament($warehouseId);
    }

    public function getInventoriesByWarehoseAndMedicament($warehouseId, $medicamentId)
    {
        return $this->inventoryRepository->getInventoriesByWarehoseAndMedicament($warehouseId, $medicamentId);
    }

    public function getTotalStockByMedicamentAndWarehouse($medicamentId, $warehouseId)
    {
        return $this->inventoryRepository->getTotalStockByMedicamentAndWarehouse($medicamentId, $warehouseId);
    }

    public function updateInventory($id, array $inventoryData)
    {
        return $this->inventoryRepository->update($id, $inventoryData);
    }

    public function deleteInventoriesByPurchaseNoteId($purchaseNoteId)
    {
        return $this->inventoryRepository->deleteByPurchaseNoteId($purchaseNoteId);
    }

    public function deleteInventoriesByMedicamentAndWarehouse($medicamentId, $warehouseId)
    {
        return $this->inventoryRepository->deleteByMedicamentAndWarehouse($medicamentId, $warehouseId);
    }

    public function getInventoryByPurchaseNoteDetailId(int $purchaseNoteDetailId)
    {
        return $this->inventoryRepository->findByPurchaseNoteDetailId($purchaseNoteDetailId);
    }

    public function deleteByPurchaseNoteDetailId(int $purchaseNoteDetailId)
    {
        return $this->inventoryRepository->deleteByPurchaseNoteDetailId($purchaseNoteDetailId);
    }

    public function updateInventoryStock($inventoryId, $quantitySold)
    {
        return $this->inventoryRepository->updateInventoryStock($inventoryId, $quantitySold);
    }

    public function restoreInventoryStock($inventoryId, $quantityRestored)
    {
        return $this->inventoryRepository->restoreInventoryStock($inventoryId, $quantityRestored);
    }

    public function getByMedicamentAndWarehouse(int $medId, int $warehouseId)
    {
        return $this->inventoryRepository
            ->findByMedicamentAndWarehouse($warehouseId, $medId)
            ->first();
    }

    public function adjustStock(int $inventoryId, int $delta)
    {
        $inv = $this->inventoryRepository->findById($inventoryId);
        $inv->stock += $delta;
        $inv->save();
    }

    public function consumeStock(int $warehouseId, int $medicamentId, int $quantity, int $salesDetailId): void
    {
        $remaining = $quantity;
        $lots = Inventory::where('warehouse_id', $warehouseId)
            ->where('medicament_id', $medicamentId)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        foreach ($lots as $lot) {
            if ($remaining <= 0) break;
            $take = min($lot->stock, $remaining);
            $lot->stock -= $take;
            $lot->save();

            StockMovement::create([
                'inventory_id'         => $lot->id,
                'sales_note_detail_id' => $salesDetailId,
                'quantity'             => -$take,
            ]);

            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \Exception("Stock insuficiente para el medicamento $medicamentId");
        }
    }

    public function restoreStockForSalesDetail(int $salesDetailId): void
    {
        $movements = StockMovement::where('sales_note_detail_id', $salesDetailId)->get();
        foreach ($movements as $mov) {
            $lot = Inventory::find($mov->inventory_id);
            if ($lot) {
                $lot->stock += abs($mov->quantity);
                $lot->save();
            }
        }
        StockMovement::where('sales_note_detail_id', $salesDetailId)->delete();
    }
}
