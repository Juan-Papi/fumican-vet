<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CategoryService;
use App\Services\Sales\MedicamentService;
use App\Http\Requests\Sales\StoreMedicamentRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class MedicamentController extends Controller
{

    public function __construct(protected MedicamentService $medicamentService) {}

    public function index()
    {
        $medicaments = $this->medicamentService->getAllMedicaments();
        return Inertia::render('Sales/Medicaments/Index', [
            'medicaments' => $medicaments,
        ]);
    }

    public function create(CategoryService $categoryService)
    {
        $categories = $categoryService->getAllCategoriesWithoutPaginate();

        return Inertia::render('Sales/Medicaments/Form', [
            'formAction' => 'create',
            'categories' => $categories,
        ]);
    }

    public function store(StoreMedicamentRequest $request)
    {
        $this->medicamentService->createMedicament($request->validated());
        return redirect()->route('medicament.index');
    }
}
