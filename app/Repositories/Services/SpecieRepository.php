<?php

namespace App\Repositories\Services;

use App\Models\Services\Specie;

class SpecieRepository
{
    public function __construct(protected Specie $model) {}

    public function getAll()
    {
        return $this->model
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
        return $this->model->where('id', $id)->update($data);
    }

    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    public function search()
    {
        $search = request('search');
        return $this->model
            ->whereLike('name', "%$search%")
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
    }

    public function findOrCreate($name)
    {
        return $this->model->firstOrCreate(['name' => $name]);
    }
}
