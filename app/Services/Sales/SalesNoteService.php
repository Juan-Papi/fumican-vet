<?php

namespace App\Services\Sales;

use App\Repositories\Sales\SalesNoteRepository;

class SalesNoteService
{
    public function __construct(protected SalesNoteRepository $salesNoteRepository) {}

    public function getAllSalesNote()
    {
        return $this->salesNoteRepository->getAll();
    }

    public function getSalesNoteById($id)
    {
        return $this->salesNoteRepository->findById($id);
    }

    public function createSalesNoteDetail(array $salesNoteData)
    {
        return $this->salesNoteRepository->create($salesNoteData);
    }
}
