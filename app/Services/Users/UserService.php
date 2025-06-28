<?php

namespace App\Services\Users;

use App\Repositories\Users\UserRepository;

class UserService
{
    public function __construct(protected UserRepository $repository) {}

    public function getAllPaginated()
    {
        return $this->repository->getAllPaginated();
    }

    public function search(array $filters)
    {
        return $this->repository->search($filters);
    }

    public function getById(string $id)
    {
        return $this->repository->getById($id);
    }

    public function create(array $data)
    {
        // Si no se provee una contraseña, se genera una automáticamente.
        if (empty($data['password'])) {
            $firstName = trim($data['first_name']);
            $lastName = trim($data['last_name']);
            $initialLastName = substr($lastName, 0, 1);
            $firstNameParts = explode(' ', $firstName);
            $firstNameOnly = $firstNameParts[0];
            $data['password'] = $firstNameOnly . $initialLastName . now()->year;
        }
        return $this->repository->create($data);
    }

    public function update(string $id, array $data)
    {
        // Si la contraseña está vacía, la eliminamos del array
        // para no actualizarla a un valor nulo.
        if (empty($data['password'])) {
            unset($data['password']);
        }
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }
}
