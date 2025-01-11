<?php

namespace App\Repositories\Users;

use App\Models\Users\Permission;

class PermissionRepository
{
    public function __construct(protected Permission $model) {}

    public function getAll()
    {
        $query = $this->model->query()
            ->when(request()->boolean('with_roles'), function ($query) {
                $query->with('roles:id,name');
            })
            ->orderBy('updated_at', 'asc');

        return $query->get();
    }

    public function findById($id)
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
        return $this->model->where('id', $id)->update($data);
    }
}
