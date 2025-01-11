<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Inventory;

class InventoryRepository
{

    public function getAll()
    {
        return Inventory::orderBy('updated_at', 'desc')
            ->with(['medicament', 'warehouse'])
            ->paginate();
    }

    public function findById($id)
    {
        return Inventory::findOrFail($id);
    }

    public function create(array $userData)
    {
        return Inventory::create($userData);
    }

    public function getGroupedInventoriesByMedicament($warehouseId)
    {
        return  Inventory::with('medicament')
            ->where('warehouse_id', $warehouseId)
            ->selectRaw('medicament_id, sum(stock) as total_stock')
            ->groupBy('medicament_id')
            ->get();
    }

    public function getInventoriesByWarehoseAndMedicament($warehouseId, $medicamentId)
    {
        return Inventory::where('warehouse_id', $warehouseId)
            ->where('medicament_id', $medicamentId)
            ->with(['medicament', 'warehouse'])
            ->orderBy('created_at', 'desc')
            ->paginate();
    }

    public function getTotalStockByMedicamentAndWarehouse($medicamentId, $warehouseId)
    {
        return Inventory::where('warehouse_id', $warehouseId)
            ->where('medicament_id', $medicamentId)
            ->sum('stock');
    }

    public function update($id, array $data)
    {
        return Inventory::where('id', $id)->update($data);
    }

    public function deleteByPurchaseNoteId($purchaseNoteId)
    {
        return Inventory::where('purchase_note_id', $purchaseNoteId)->delete();
    }

    public function deleteByMedicamentAndWarehouse($medicamentId, $warehouseId)
    {
        return Inventory::where('medicament_id', $medicamentId)
            ->where('warehouse_id', $warehouseId)
            ->delete();
    }
}
