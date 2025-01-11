<?php
namespace App\Services\Users;

use App\Repositories\Users\RoleRepository;

class RoleService
{
    public function __construct(protected RoleRepository $repository) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getAllPaginated()
    {
        return $this->repository->getAllPaginated();
    }

    public function getById(string $id)
    {
        return $this->repository->getById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data)
    {
        return $this->repository->update($id, $data);
    }
}
