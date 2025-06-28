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

    // AÑADIDO: Lógica de búsqueda avanzada
    public function search(array $filters, bool $paginate = true)
    {
        $query = $this->model->with([
            'pet:id,name,customer_id,breed_id',
            'pet.owner:id,first_name,last_name,ci',
            'pet.breed.specie:id,name'
        ]);

        if (!empty($filters['search_term'])) {
            $term = $filters['search_term'];
            $query->where(function ($q) use ($term) {
                $q->where('reason', 'like', "%{$term}%")
                    ->orWhereHas('pet', function ($qPet) use ($term) {
                        $qPet->where('name', 'like', "%{$term}%")
                            ->orWhereHas('owner', function ($qOwner) use ($term) {
                                $qOwner->where('first_name', 'like', "%{$term}%")
                                    ->orWhere('last_name', 'like', "%{$term}%");
                            });
                    });
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $query->orderBy('created_at', 'desc');

        if ($paginate) {
            return $query->paginate()->appends($filters);
        }

        return $query->get();
    }
}
