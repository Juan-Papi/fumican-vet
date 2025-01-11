<?php

namespace App\Services\Sales;

use App\Repositories\Sales\SalesNoteDetailRepository;

class SalesNoteDetailService
{
    public function __construct(protected SalesNoteDetailRepository $salesNoteDetailRepository) {}

    public function getAllSalesNoteDetails()
    {
        return $this->salesNoteDetailRepository->getAll();
    }

    public function getPurchaseNoteDetailById($id)
    {
        return $this->salesNoteDetailRepository->findById($id);
    }

    public function createPurchaseNoteDetail(array $salesNoteDetailData)
    {
        return $this->salesNoteDetailRepository->create($salesNoteDetailData);
    }

    public function getSalesNoteDetailsBySalesNoteId($salesNoteId)
    {
        return $this->salesNoteDetailRepository->findBySalesNoteId($salesNoteId);
    }
}
