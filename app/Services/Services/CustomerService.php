<?php

namespace App\Services\Services;

use App\Repositories\Services\CustomerRepository;

class CustomerService
{
    public function __construct(protected CustomerRepository $customerRepository) {}

    public function getAllCustomers()
    {
        return $this->customerRepository->getAll();
    }

    public function getCustomerById($id)
    {
        return $this->customerRepository->findById($id);
    }

    public function createCustomer(array $userData)
    {
        return $this->customerRepository->create($userData);
    }

    public function update(array $userData, $id)
    {
        return $this->customerRepository->update($userData, $id);
    }

    public function search()
    {
        return $this->customerRepository->search();
    }
}
