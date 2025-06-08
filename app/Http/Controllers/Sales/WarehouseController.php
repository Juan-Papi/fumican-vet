<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreInventoryRequest;
use App\Services\Sales\WarehouseService;
use App\Services\Sales\InventoryService;
use App\Services\Sales\MedicamentService;
use App\Http\Requests\Sales\StoreWarehouseRequest;
use App\Models\Sales\Inventory;
use Illuminate\Http\Request;
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

    public function create()
    {
        return Inertia::render('Sales/Warehouses/Form', [
            'formAction' => 'create',
        ]);
    }

    public function store(StoreWarehouseRequest $request)
    {
        $this->warehouseService->createWarehouse($request->validated());
        return redirect()->route('warehouse.index');
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

    public function showInventoryMedicament(int $warehouseId, int $medicamentId, InventoryService $inventoryService, MedicamentService $medicamentService)
    {
        $inventories = $inventoryService->getInventoriesByWarehoseAndMedicament($warehouseId, $medicamentId);
        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        $medicament = $medicamentService->getMedicamentById($medicamentId);
        // if($inventories->isEmpty()) {
        //    dd('esta vacio');
        // }
        // return $warehouse;
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
}
