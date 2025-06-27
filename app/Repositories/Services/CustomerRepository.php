<?php

namespace App\Repositories\Services;

use App\Models\Services\Customer;

class CustomerRepository
{
    public function __construct(protected Customer $model) {}

    public function getAll()
    {
        return $this->model->orderBy('updated_at', 'desc')->paginate();
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $userData)
    {
        return $this->model->create($userData);
    }

    public function update(array $data, $id)
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * CORREGIDO: Método de búsqueda ahora maneja filtros y puede paginar.
     */
    public function search(array $filters, bool $paginate = true)
    {
        $query = $this->model->query();

        if (!empty($filters['search_term'])) {
            $term = $filters['search_term'];
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('ci', 'like', "%{$term}%");
            });
        }

        $query->orderBy('updated_at', 'desc');

        if ($paginate) {
            return $query->paginate(15)->appends($filters);
        }

        return $query->take(8)->get();
    }
}
