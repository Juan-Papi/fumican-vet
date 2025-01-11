<?php

namespace App\Services\Services;

use App\Repositories\Services\MedicalConsultationRepository;

class MedicalConsultationService
{
    public function __construct(protected MedicalConsultationRepository $repository) {}

    public function getAll()
    {
        $medicalConsultations = $this->repository->getAll();
        foreach ($medicalConsultations as $mc) {
            $mc->pet_name = $mc->pet->name;
            $mc->pet_owner = $mc->pet->owner->first_name . ' ' . $mc->pet->owner->last_name;
        }
        return $medicalConsultations;
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->repository->update($data, $id);
    }
}
