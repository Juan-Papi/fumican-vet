<?php

namespace App\Services\Sales;

use App\Repositories\Sales\PurchaseNoteDetailRepository;

class PurchaseNoteDetailService
{
    public function __construct(protected PurchaseNoteDetailRepository $purchaseNoteDetailRepository) {}

    public function getAllPurchaseNoteDetails()
    {
        return $this->purchaseNoteDetailRepository->getAll();
    }

    public function getPurchaseNoteDetailById($id)
    {
        return $this->purchaseNoteDetailRepository->findById($id);
    }

    public function createPurchaseNoteDetail(array $purchaseNoteDetailData)
    {
        return $this->purchaseNoteDetailRepository->create($purchaseNoteDetailData);
    }

    public function getPurchaseNoteDetailsByPurchaseNoteId($purchaseNoteId)
    {
        return $this->purchaseNoteDetailRepository->findByPurchaseNoteId($purchaseNoteId);
    }

    public function deleteByMedicamentAndPurchaseNoteId($medicamentId, $purchaseNoteId)
    {
        return $this->purchaseNoteDetailRepository->deleteByMedicamentAndPurchaseNoteId($medicamentId, $purchaseNoteId);
    }

    public function updatePurchaseNoteDetail($id, array $purchaseNoteDetailData)
    {
        return $this->purchaseNoteDetailRepository->update($id, $purchaseNoteDetailData);
    }

    public function deleteById($id)
    {
        return $this->purchaseNoteDetailRepository->deleteById($id);
    }
}
