<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\CategoryService;
use App\Services\Sales\MedicamentService;
use App\Http\Requests\Sales\StoreMedicamentRequest;
use App\Services\Sales\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
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

    public function store(StoreMedicamentRequest $request): JsonResponse
    {
        $med = $this->medicamentService->createMedicament($request->validated());
        return response()->json([
            'message'    => 'Medicamento creado correctamente',
            'medicament' => $med
        ], 201);
    }

    public function update(StoreMedicamentRequest $request, int $id): JsonResponse
    {
        $this->medicamentService->updateMedicament($id, $request->validated());
        return response()->json([
            'message' => 'Medicamento actualizado correctamente'
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $this->medicamentService->deleteMedicament($id);
        return response()->json([
            'message' => 'Medicamento eliminado correctamente'
        ]);
    }
}
