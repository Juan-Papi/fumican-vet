<?php

namespace App\Services\Sales;

use App\Repositories\Sales\MedicamentRepository;

class MedicamentService
{
    public function __construct(protected MedicamentRepository $medicamentRepository) {}

    public function getAllMedicaments()
    {
        return $this->medicamentRepository->getAll();
    }

    public function getMedicamentById($id)
    {
        return $this->medicamentRepository->findById($id);
    }

    public function createMedicament(array $userData)
    {
        return $this->medicamentRepository->create($userData);
    }

    public function search()
    {
        return $this->medicamentRepository->search();
    }

    public function updateMedicament($id, array $data)
    {
        return $this->medicamentRepository->update($id, $data);
    }

    public function deleteMedicament($id)
    {
        return $this->medicamentRepository->delete($id);
    }

}
