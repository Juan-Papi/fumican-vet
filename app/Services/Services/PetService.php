<?php

namespace App\Services\Services;

use App\Repositories\Services\PetRepository;

class PetService
{
    public function __construct(protected PetRepository $repository) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        // return $this->repository->delete($id);
    }

    public function search($search)
    {
        $pets = $this->repository->search($search);
        foreach ($pets as $pet) {
            $pet->owner_full_name = $pet->owner->first_name . ' ' . $pet->owner->last_name;
            $pet->specie_and_breed = $pet->breed->specie->name . ' - ' . $pet->breed->name;
        }
        return $pets;
    }
}
