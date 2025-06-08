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

    public function __construct(protected MedicamentService $medicamentService, protected CategoryService $categoryService) {}

    public function index()
    {
        $medicaments = $this->medicamentService->getAllMedicaments();
        $categories  = $this->categoryService->getAllCategoriesWithoutPaginate();
        return Inertia::render('Sales/Medicaments/Index', [
            'medicaments' => $medicaments,
            'categories'  => $categories,
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

    public function update(StoreMedicamentRequest $request, int $id)
    {
        $this->medicamentService->updateMedicament($id, $request->validated());

        return redirect()
            ->route('medicament.index')
            ->with('success', 'Medicamento actualizado correctamente');
    }
}
