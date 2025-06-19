<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreInventoryRequest;
use App\Services\Sales\WarehouseService;
use App\Services\Sales\InventoryService;
use App\Services\Sales\MedicamentService;
use App\Http\Requests\Sales\StoreWarehouseRequest;
use App\Http\Requests\Sales\UpdateInventoryRequest;
use App\Models\Sales\Inventory;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function __construct(protected WarehouseService $warehouseService) {}

    public function index()
    {
        $warehouses = $this->warehouseService->getAllWarehouses();
        return Inertia::render('Sales/Warehouses/Index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseService->createWarehouse($request->validated());

        return response()->json([
            'message'   => "Almacén «{$warehouse->name}» creado correctamente",
            'warehouse' => $warehouse,
        ], 201);
    }


    public function show(int $warehouseId, InventoryService $inventoryService)
    {
        $inventories = $inventoryService->getGroupedInventoriesByMedicament($warehouseId);
        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        return Inertia::render('Sales/Warehouses/Show', [
            'warehouse' => $warehouse,
            'inventories' => $inventories
        ]);
    }

    // Actualizar almacén
    public function update(StoreWarehouseRequest $request, int $id): JsonResponse
    {
        // Esto devuelve un int…
        $rows = $this->warehouseService->updateWarehouse($id, $request->validated());
        // …así que vuelves a buscar el modelo
        $warehouse = $this->warehouseService->getWarehouseById($id);

        return response()->json([
            'message'   => "Almacén «{$warehouse->name}» actualizado correctamente",
            'warehouse' => $warehouse,
        ]);
    }


    // Eliminar almacén (usamos POST para facilitar integración con Inertia y evitar problemas con DELETE)
    public function destroy(int $id): JsonResponse
    {
        // Primero obtenemos el modelo para extraer el nombre
        $warehouse = $this->warehouseService->getWarehouseById($id);
        $name      = $warehouse->name;

        // Luego lo borramos
        $this->warehouseService->deleteWarehouse($id);

        return response()->json([
            'message' => "Almacén «{$name}» eliminado correctamente",
        ]);
    }


    public function showInventoryMedicament(int $warehouseId, int $medicamentId, InventoryService $inventoryService, MedicamentService $medicamentService)
    {
        $inventories = $inventoryService->getInventoriesByWarehoseAndMedicament($warehouseId, $medicamentId);
        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        $medicament = $medicamentService->getMedicamentById($medicamentId);
        return Inertia::render('Sales/Warehouses/ShowInventoryMedicament', [
            'warehouse' => $warehouse,
            'inventories' => $inventories,
            'medicament' => $medicament
        ]);
    }

    public function storeInventory(StoreInventoryRequest $request, $warehouseId, $medicamentId)
    {
        $data = $request->validated();
        $data['warehouse_id'] = $warehouseId;
        $data['medicament_id'] = $medicamentId;
        // como es lote manual, no asociamos purchase_note_detail
        $data['purchase_note_detail_id'] = null;

        Inventory::create($data);

        return back()->with('success', 'Lote agregado exitosamente');
    }

    public function updateInventory(
        UpdateInventoryRequest $request,
        int $warehouseId,
        int $medicamentId,
        int $inventoryId
    ) {
        $data = $request->validated();

        $inventory = Inventory::where('warehouse_id', $warehouseId)
            ->where('medicament_id', $medicamentId)
            ->findOrFail($inventoryId);

        $inventory->update($data);

        return back()->with('success', 'Lote actualizado correctamente');
    }

    public function destroyInventory(
        int $warehouseId,
        int $medicamentId,
        int $inventoryId
    ) {
        $inv = Inventory::where('warehouse_id', $warehouseId)
            ->where('medicament_id', $medicamentId)
            ->findOrFail($inventoryId);

        $inv->delete();

        return back()->with('success', 'Lote eliminado correctamente');
    }
}
