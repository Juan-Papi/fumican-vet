<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CategoryService;
use App\Services\Sales\MedicamentService;
use App\Http\Requests\Sales\StoreMedicamentRequest;
use App\Services\Sales\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class MedicamentController extends Controller
{

    public function __construct(protected MedicamentService $medicamentService, protected CategoryService $categoryService, protected WarehouseService $warehouseService) {}

    public function index()
    {
        $medicaments = $this->medicamentService->getAllMedicaments();
        $categories  = $this->categoryService->getAllCategoriesWithoutPaginate();
        $warehouses = $this->warehouseService->getAllWarehousesWithoutPaginate();
        return Inertia::render('Sales/Medicaments/Index', [
            'medicaments' => $medicaments,
            'categories'  => $categories,
            'warehouses'  => $warehouses,
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

    public function destroy(int $id, Request $request)
    {
        $this->medicamentService->deleteMedicament($id);

        return redirect()->route('medicament.index')->with('success', 'Medicamento eliminado correctamente');
    }
}
