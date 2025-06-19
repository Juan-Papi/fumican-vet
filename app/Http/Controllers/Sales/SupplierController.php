<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SupplierService;
use App\Http\Requests\Sales\StoreSupplierRequest;
use App\Http\Requests\Sales\UpdateSupplierRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{

    public function __construct(protected SupplierService $supplierService) {}

    public function index()
    {
        $suppliers = $this->supplierService->getAllSuppliers();
        return Inertia::render('Sales/Suppliers/Index', compact('suppliers'));
    }

    public function store(StoreSupplierRequest $request)
    {
        // DEBUG: Ver qué datos llegan
        Log::info('Datos recibidos en store:', $request->all());
        Log::info('Datos validados:', $request->validated());

        try {
            $supplier = $this->supplierService->createSupplier($request->validated());
            Log::info('Proveedor creado exitosamente:', $supplier->toArray());
            return response()->json($supplier, 201);
        } catch (\Exception $e) {
            Log::error('Error al crear proveedor:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    public function update(UpdateSupplierRequest $request, int $id)
    {
        $this->supplierService->updateSupplier($id, $request->validated());
        return response()->json(['message' => 'Proveedor actualizado correctamente']);
    }

    public function destroy(int $id)
    {
        $this->supplierService->deleteSupplier($id);
        return response()->json(['message' => 'Proveedor eliminado correctamente']);
    }
}
