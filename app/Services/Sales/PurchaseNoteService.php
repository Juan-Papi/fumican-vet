<?php

namespace App\Services\Sales;

use App\Repositories\Sales\PurchaseNoteRepository;
use App\Models\Sales\PurchaseNote;
use App\Models\Sales\PurchaseNoteDetail;
use App\Models\Sales\Inventory;

class PurchaseNoteService
{
    public function __construct(protected PurchaseNoteRepository $purchaseNoteRepository) {}

    public function getAllPurchaseNotes()
    {
        return $this->purchaseNoteRepository->getAll();
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
}
