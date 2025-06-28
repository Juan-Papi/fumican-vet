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
        $query = $this->model->with(['owner:id,first_name,last_name,ci', 'breed.specie:id,name']);

        if (!empty($filters['search_term'])) {
            $term = $filters['search_term'];
            // ¡Esta parte ya está correcta! Usa el closure ->where(function($q){...})
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhereHas('owner', function ($qOwner) use ($term) {
                        $qOwner->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('ci', 'like', "%{$term}%"); // AÑADIDO: Buscar también por CI
                    });
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate()->appends($filters);
    }


    /**
     * CORREGIDO: El método de búsqueda original, ahora renombrado y con la lógica de consulta correcta.
     */
    public function autocompleteSearch(string $term)
    {
        $query = $this->model
            ->with(['owner:id,ci,first_name,last_name', 'breed.specie:id,name']);

        // CORRECCIÓN: Envolver la lógica 'OR' en un closure para evitar resultados inesperados.
        $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhereHas('owner', function ($qOwner) use ($term) {
                    $qOwner->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('ci', 'like', "%{$term}%"); // AÑADIDO: Buscar también por CI
                });
        });

        return $query->orderBy('name', 'asc') // Ordenar por nombre es más útil para autocompletado
            ->take(10) // Limitar a 10 resultados es suficiente
            ->get();
    }
}
