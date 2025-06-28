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

    public function delete($id)
    {
        return $this->customerRepository->delete($id);
    }

    /**
     * CORREGIDO: Método de búsqueda para la tabla principal (paginado).
     */
    public function search(array $filters)
    {
        return $this->customerRepository->search($filters, true);
    }

    /**
     * CORREGIDO: Método para el autocompletado (no paginado).
     */
    public function autocompleteSearch(?string $term)
    {
        if (empty($term)) {
            return [];
        }
        return $this->customerRepository->search(['search_term' => $term], false);
    }
}
