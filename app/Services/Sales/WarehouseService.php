<?php

namespace App\Services\Sales;

use App\Repositories\Sales\WarehouseRepository;

class WarehouseService
{
    public function __construct(protected WarehouseRepository $warehouseRepository) {}

    public function getAllWarehouses()
    {
        return $this->warehouseRepository->getAll();
    }

    public function getAllWarehousesWithoutPaginate()
    {
        return $this->warehouseRepository->getAllWithoutPaginate();
    }


    public function getWarehouseById($id)
    {
        return $this->warehouseRepository->findById($id);
    }

    public function createWarehouse(array $userData)
    {
        return $this->warehouseRepository->create($userData);
    }

    public function search()
    {
        return $this->warehouseRepository->search();
    }

    public function getGroupedInventories($id)
    {
        return $this->warehouseRepository->getGroupedInventories($id);
    }
}
