<?php

namespace App\Repositories\Services;

use App\Models\Services\Pet;

class PetRepository
{
    public function __construct(protected Pet $model) {}

    public function getAll()
    {
        return $this->model
            // CORREGIDO: Añadido 'ci' a la lista de columnas del propietario.
            ->with(['owner:id,first_name,last_name,ci', 'breed.specie:id,name'])
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

    public function update($id, array $data)
    {
        $pet = $this->model->findOrFail($id);
        return $pet->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * AÑADIDO: Lógica de búsqueda para la tabla principal, paginada.
     */
    public function searchWithFilters(array $filters)
    {
        // CORREGIDO: Añadido 'ci' a la lista de columnas del propietario.
        $query = $this->model->with(['owner:id,first_name,last_name,ci', 'breed.specie:id,name']);

        if (!empty($filters['search_term'])) {
            $term = $filters['search_term'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhereHas('owner', function ($qOwner) use ($term) {
                        $qOwner->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%");
                    });
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate()->appends($filters);
    }


    /**
     * CORREGIDO: El método de búsqueda original, ahora renombrado para el autocompletado.
     */
    public function autocompleteSearch(string $term)
    {
        return $this->model
            ->with(['owner:id,ci,first_name,last_name', 'breed.specie:id,name'])
            ->where('name', 'like', "%{$term}%")
            ->orWhereHas('owner', function ($query) use ($term) {
                $query->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%");
            })
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();
    }
}
