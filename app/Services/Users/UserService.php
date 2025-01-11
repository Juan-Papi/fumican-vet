<?php

namespace App\Services\Users;

use App\Repositories\Users\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $repository) {}

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
        $firstName = trim($data['first_name']);
        $lastName = trim($data['last_name']);
        $initialLastName = substr($lastName, 0, 1);
        $data['password'] = $firstName . $initialLastName . date('Y');
        return $this->repository->create($data);
    }

    public function update(string $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findByEmail($email);
    }
}
