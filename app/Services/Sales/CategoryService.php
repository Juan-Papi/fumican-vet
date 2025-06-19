<?php

namespace App\Services\Sales;

use App\Repositories\Sales\CategoryRepository;

class CategoryService
{
    public function __construct(protected CategoryRepository $categoryRepository) {}

    public function getAllCategories()
    {
        return $this->categoryRepository->getAll();
    }

    public function getAllCategoriesWithoutPaginate()
    {
        return $this->categoryRepository->getAllWithoutPaginate();
    }

    public function getCategoryById($id)
    {
        return $this->categoryRepository->findById($id);
    }

    public function createCategory(array $userData)
    {
        return $this->categoryRepository->create($userData);
    }

    public function updateCategory(int $id, array $userData)
    {
        return $this->categoryRepository->update($id, $userData);
    }

    public function deleteCategory(int $id)
    {
        return $this->categoryRepository->findById($id)->delete();
    }

    public function search()
    {
        return $this->categoryRepository->search();
    }
}
