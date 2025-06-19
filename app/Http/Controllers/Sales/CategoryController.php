<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CategoryService;
use App\Http\Requests\Sales\StoreCategoryRequest;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return Inertia::render('Sales/Categories/Index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->validated());
        return response()->json($category, 201);
    }

    public function update(StoreCategoryRequest $request, int $id): JsonResponse
    {
        $this->categoryService->updateCategory($id, $request->validated());
        return response()->json([
            'message' => 'Categoría actualizada correctamente'
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoryService->deleteCategory($id);
        return response()->json([
            'message' => 'Categoría eliminada correctamente'
        ]);
    }
}
