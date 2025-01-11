<?php

namespace App\Services\Services;

use App\Repositories\Services\BreedRepository;
use DASPRiD\Enum\Exception\IllegalArgumentException;

class BreedService
{
    public function __construct(protected BreedRepository $repository) {}

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

    public function search()
    {
        return $this->repository->search();
    }

    public function findOrCreate($name, $specieId)
    {
        if (empty($name)) {
            return throw new IllegalArgumentException('Debe ingresar un nombre para la raza');
        }
        return $this->repository->findOrCreate($name, $specieId);
    }
}
