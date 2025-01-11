<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CategoryService;
use App\Http\Requests\Sales\StoreCategoryRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index()
    {
        $categories = $this->categoryService->getAllCategories();
        return Inertia::render('Sales/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return Inertia::render(
            'Sales/Categories/Form',
            ['formAction' => 'create']
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->createCategory($request->validated());
        return redirect()->route('category.index');
    }
}
