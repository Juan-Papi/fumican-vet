<?php

namespace App\Services\Services;

use App\Repositories\Services\SpecieRepository;
use DASPRiD\Enum\Exception\IllegalArgumentException;

class SpecieService
{
    public function __construct(protected SpecieRepository $repository) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    public function create(array $userData)
    {
        return $this->repository->create($userData);
    }

    public function search()
    {
        return $this->repository->search();
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function findOrCreate($name)
    {
        if (empty($name)) {
            return throw new IllegalArgumentException('Debe ingresar un nombre para la especie');
        }
        return $this->repository->findOrCreate($name);
    }
}
