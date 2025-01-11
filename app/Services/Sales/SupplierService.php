<?php

namespace App\Services\Sales;

use App\Repositories\Sales\SupplierRepository;

class SupplierService
{
    public function __construct(protected SupplierRepository $supplierRepository) {}

    public function getAllSuppliers()
    {
        return $this->supplierRepository->getAll();
    }

    public function getSupplierById($id)
    {
        return $this->supplierRepository->findById($id);
    }

    public function createSupplier(array $userData)
    {
        return $this->supplierRepository->create($userData);
    }

    public function search()
    {
        return $this->supplierRepository->search();
    }
}
