<?php

namespace App\Services\Sales;

use App\Models\Sales\PurchaseNote;
use App\Repositories\Sales\PurchaseNoteRepository;

class PurchaseNoteService
{
    public function __construct(protected PurchaseNoteRepository $purchaseNoteRepository) {}

    public function getAllPurchaseNotes(array $filters = [], bool $paginate = true)
    {
        return $this->purchaseNoteRepository->getAll($filters, $paginate);
    }

    public function getPurchaseNoteById($id)
    {
        return $this->purchaseNoteRepository->findById($id);
    }

    public function createPurchaseNote(array $purchaseNoteData)
    {
        return $this->purchaseNoteRepository->create($purchaseNoteData);
    }

    public function updatePurchaseNote($id, array $purchaseNoteData)
    {
        return $this->purchaseNoteRepository->update($id, $purchaseNoteData);
    }

    public function deletePurchaseNoteById($id)
    {
        return $this->purchaseNoteRepository->deleteById($id);
    }

    public function getFilteredPurchaseNotes($filters, $paginate = true)
    {
        $query = PurchaseNote::with(['supplier', 'warehouse']);

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('purchase_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('purchase_date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;
        if ($paginate) {
            return $query->orderBy('id', 'desc')->paginate($perPage);
        } else {
            return $query->orderBy('id', 'desc')->get();
        }
    }
}
