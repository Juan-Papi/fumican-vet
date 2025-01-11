<?php

namespace App\Repositories\Users;

use App\Models\Users\Role;

class RoleRepository
{
    public function __construct(protected Role $model) {}

    public function getAll()
    {
        $query = $this->model->query()
            ->when(request()->boolean('with_permissions'), function ($query) {
                $query->with('permissions:id,name');
            })
            ->orderBy('updated_at', 'asc');

        if (request()->has('page')) {
            return $query->paginate();
        }
        return $query->get();
    }

    public function getAllPaginated()
    {
        $query = $this->model->query()
            ->with('permissions:id,name')
            ->orderBy('updated_at', 'asc');

        return $query->paginate();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function findByName($name)
    {
        return $this->model->where('name', $name)->first();
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
