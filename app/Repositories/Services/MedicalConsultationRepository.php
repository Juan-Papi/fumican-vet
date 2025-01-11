<?php

namespace App\Repositories\Services;

use App\Models\Services\MedicalConsultation;

class MedicalConsultationRepository
{
    public function __construct(protected MedicalConsultation $model) {}

    public function getAll()
    {
        return $this->model
            ->select('id', 'pet_id', 'created_at', 'reason', 'updated_at')
            ->with(['pet:id,name,customer_id', 'pet.owner:id,first_name,last_name'])
            ->orderBy('updated_at', 'desc')
            ->paginate();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $mc = $this->model->findOrFail($id);
        return $mc->update($data);
    }
}
