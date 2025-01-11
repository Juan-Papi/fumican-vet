<?php

namespace App\Services\Sales;

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
}
