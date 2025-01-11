<?php
namespace App\Services\Users;

use App\Repositories\Users\PermissionRepository;

class PermissionService
{
    public function __construct(protected PermissionRepository $repository) {}

    public function getAll()
    {
        return $this->repository->getAll();
    }
}
