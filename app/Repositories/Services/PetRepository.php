<?php

namespace App\Repositories\Services;

use App\Models\Services\Pet;

class PetRepository
{
    public function __construct(protected Pet $model) {}

    public function getAll()
    {
        return $this->model
            ->with(['owner:id,first_name,last_name', 'breed' => function ($query) {
                $query->with('specie:id,name');
            }])
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

    public function search($search)
    {
        return $this->model
            ->with(['owner:id,ci,first_name,last_name', 'breed' => function ($query) {
                $query->with('specie:id,name');
            }])
            ->whereLike('name', "%$search%")
            ->orWhereHas('owner', function ($query) use ($search) {
                $query->whereLike('first_name', "%$search%")
                    ->orWhereLike('last_name', "%$search%");
            })
            ->orderBy('updated_at', 'desc')
            ->take(8)
            ->get();
    }
}
