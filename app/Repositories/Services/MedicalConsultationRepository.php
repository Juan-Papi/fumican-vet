<?php

namespace App\Repositories\Services;

use App\Models\Services\MedicalConsultation;

class MedicalConsultationRepository
{
    public function __construct(protected MedicalConsultation $model) {}

    public function getAllWithDetails()
    {
        // El método with() carga las relaciones para evitar problemas de N+1
        // y asegura que los datos estén disponibles.
        return $this->model
            ->with([
                'pet:id,name,customer_id,breed_id',
                'pet.owner:id,first_name,last_name,ci',
                'pet.breed.specie:id,name'
            ])
            ->orderBy('updated_at', 'desc')
            ->paginate();
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

    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
