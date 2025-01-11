<?php

namespace App\Repositories\Users;

use App\Models\User;

class UserRepository
{
    public function __construct(protected User $model) {}

    public function getAll()
    {
        $query = $this->model->query()
            ->orderBy('updated_at', 'desc');

        if (request()->has('page')) {
            return $query->paginate();
        }
        return $query->get();
    }

    public function getAllPaginated()
    {
        $query = $this->model->query()
            ->with('permissions:id,name')
            ->orderBy('updated_at', 'desc');

        return $query->paginate();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $role = $this->model->findOrFail($id);
        return $role->update($data);
    }
}
