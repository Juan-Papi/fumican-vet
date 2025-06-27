<?php

namespace App\Repositories\Users;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model) {}

    public function getAllPaginated()
    {
        return $this->model->with('roles:id,name')->orderBy('updated_at', 'desc')->paginate();
    }

    public function search(array $filters)
    {
        $query = $this->model->with('roles:id,name');

        if (!empty($filters['search_term'])) {
            $term = $filters['search_term'];
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate()->appends($filters);
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->model->findOrFail($id);
        return $user->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
