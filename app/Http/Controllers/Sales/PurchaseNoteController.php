<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StorePurchaseNoteRequest;
use App\Http\Requests\Sales\UpdatePurchaseNoteRequest;
use App\Services\Sales\InventoryService;
use App\Services\Sales\MedicamentService;
use App\Services\Sales\PurchaseNoteDetailService;
use App\Services\Sales\PurchaseNoteService;
use App\Services\Sales\SupplierService;
use App\Services\Sales\WarehouseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PDF;

class PurchaseNoteController extends Controller
{
    public function __construct(protected PurchaseNoteService $purchaseNoteService, protected PurchaseNoteDetailService $purchaseNoteDetailService) {}

    public function index()
    {
        $purchases = $this->purchaseNoteService->getAllPurchaseNotes();
        return Inertia::render('Sales/PurchasesNotes/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function create(SupplierService $supplierService, WarehouseService $warehouseService, MedicamentService $medicamentService)
    {
        $suppliers = $supplierService->getAllSuppliers()->items();
        $warehouses = $warehouseService->getAllWarehouses()->items();
        $medicamentsList = $medicamentService->getAllMedicaments()->items();

        return Inertia::render('Sales/PurchasesNotes/Form', [
            'formAction' => 'create',
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'medicamentsList' => $medicamentsList,
        ]);
    }

    public function store(StorePurchaseNoteRequest $request, InventoryService $inventoryService, PurchaseNoteDetailService $purchaseNoteDetailService)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $purchaseNoteData = [
                'warehouse_id' => $data['warehouse_id'],
                'supplier_id' => $data['supplier_id'],
                'user_id' => Auth::user()->id,
                'purchase_date' => now(),
                'total_amount' => $data['total_amount'],
            ];

            $purchaseNote = $this->purchaseNoteService->createPurchaseNote($purchaseNoteData);

            foreach ($data['medicaments'] as $medicament) {
                $purchaseNoteDetailData = [
                    'quantity' => $medicament['quantity'],
                    'purchase_price' => $medicament['purchase_price'],
                    'percentage' => 0,
                    'subtotal' => $medicament['subtotal'],
                    'purchase_note_id' => $purchaseNote->id,
                    'medicament_id' => $medicament['id'],
                ];
                $purchaseNoteDetailService->createPurchaseNoteDetail($purchaseNoteDetailData);

                $inventoryData = [
                    'stock' => $medicament['quantity'],
                    'price' => $medicament['purchase_price'],
                    'warehouse_id' => $data['warehouse_id'],
                    'medicament_id' => $medicament['id'],
                ];
                $inventoryService->createInventory($inventoryData);
            }

            DB::commit();
            return redirect()->route('purchase.index')->with('success', 'Nota de compra creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al crear la nota de compra: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $purchaseNote = $this->purchaseNoteService->getPurchaseNoteById($id);
        $purchaseNoteDetails = $this->purchaseNoteDetailService->getPurchaseNoteDetailsByPurchaseNoteId($id);

        return Inertia::render('Sales/PurchasesNotes/Show', [
            'purchaseNote' => $purchaseNote,
            'purchaseNoteDetails' => $purchaseNoteDetails,
        ]);
    }

    public function generatePdf($id)
    {
        $purchaseNote = $this->purchaseNoteService->getPurchaseNoteById($id);
        $purchaseNoteDetails = $this->purchaseNoteDetailService->getPurchaseNoteDetailsByPurchaseNoteId($id);

        $pdf = PDF::loadView('pdf.purchase_note', compact('purchaseNote', 'purchaseNoteDetails'));
        return $pdf->stream('nota_de_compra.pdf');
    }

    public function edit(int $purchaseNoteId, SupplierService $supplierService, WarehouseService $warehouseService, MedicamentService $medicamentService)
    {
        $purchaseNote = $this->purchaseNoteService->getPurchaseNoteById($purchaseNoteId);
        $purchaseNoteDetails = $this->purchaseNoteDetailService->getPurchaseNoteDetailsByPurchaseNoteId($purchaseNoteId);
        $suppliers = $supplierService->getAllSuppliers()->items();
        $warehouses = $warehouseService->getAllWarehouses()->items();
        $medicamentsList = $medicamentService->getAllMedicaments()->items();

        return Inertia::render('Sales/PurchasesNotes/FormEdit', [
            'purchaseNote' => $purchaseNote,
            'purchaseNoteDetails' => $purchaseNoteDetails,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'medicamentsList' => $medicamentsList,
        ]);
    }

    public function update(UpdatePurchaseNoteRequest $request, $id, InventoryService $inventoryService, PurchaseNoteDetailService $purchaseNoteDetailService)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            // Obtener la nota de compra original
            $originalPurchaseNote = $this->purchaseNoteService->getPurchaseNoteById($id);
            $originalWarehouseId = $originalPurchaseNote->warehouse_id;

            $purchaseNoteData = [
                'warehouse_id' => $data['warehouse_id'],
                'supplier_id' => $data['supplier_id'],
                'total_amount' => $data['total_amount'],
            ];

            $this->purchaseNoteService->updatePurchaseNote($id, $purchaseNoteData);

            // Obtener los detalles existentes de la nota de compra
            $existingDetails = $purchaseNoteDetailService->getPurchaseNoteDetailsByPurchaseNoteId($id);
            $existingDetailsMap = $existingDetails->keyBy('id');

            // Eliminar inventarios asociados a los medicamentos eliminados
            $existingDetailIds = $existingDetails->pluck('id')->toArray();
            $newDetailIds = array_column($data['medicaments'], 'id');
            $detailsToDelete = array_diff($existingDetailIds, $newDetailIds);
            foreach ($detailsToDelete as $detailId) {
                $detail = $existingDetailsMap[$detailId];
                $inventoryService->deleteInventoriesByMedicamentAndWarehouse($detail->medicament_id, $originalWarehouseId);
                $purchaseNoteDetailService->deleteById($detailId);
            }

            // Actualizar o crear detalles de la nota de compra e inventarios
            foreach ($data['medicaments'] as $medicament) {
                $purchaseNoteDetailData = [
                    'quantity' => $medicament['quantity'],
                    'purchase_price' => $medicament['purchase_price'],
                    'percentage' => 0,
                    'subtotal' => $medicament['subtotal'],
                    'purchase_note_id' => $id,
                    'medicament_id' => $medicament['id'],
                ];

                if (isset($medicament['id']) && isset($existingDetailsMap[$medicament['id']])) {
                    // Actualizar detalle existente
                    $purchaseNoteDetailService->updatePurchaseNoteDetail($medicament['id'], $purchaseNoteDetailData);
                } else {
                    // Crear nuevo detalle
                    $purchaseNoteDetailService->createPurchaseNoteDetail($purchaseNoteDetailData);
                }

                $inventoryData = [
                    'stock' => $medicament['quantity'],
                    'price' => $medicament['purchase_price'],
                    'warehouse_id' => $data['warehouse_id'],
                    'medicament_id' => $medicament['id'],
                ];

                if (isset($medicament['id']) && isset($existingDetailsMap[$medicament['id']])) {
                    // Actualizar inventario existente
                    $inventoryService->updateInventory($existingDetailsMap[$medicament['id']]->inventory_id, $inventoryData);
                } else {
                    // Crear nuevo inventario
                    $inventoryService->createInventory($inventoryData);
                }
            }

            DB::commit();
            return redirect()->route('purchase.index')->with('success', 'Nota de compra actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al actualizar la nota de compra: ' . $e->getMessage()]);
        }
    }
}
