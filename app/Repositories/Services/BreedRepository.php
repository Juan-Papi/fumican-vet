<?php

namespace App\Repositories\Services;

use App\Models\Services\Breed;

class BreedRepository
{
    public function __construct(protected Breed $model) {}

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
        $specieId = (int) request('specie_id');
        return $this->model
            ->whereLike('name', "%$search%")
            ->with('specie:id,name')
            ->when($specieId, function ($query, $specieId) {
                return $query->where('specie_id', $specieId);
            })
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
    }

    public function findOrCreate($name, $specieId)
    {
        return $this->model->firstOrCreate(['name' => $name, 'specie_id' => $specieId]);
    }
}
