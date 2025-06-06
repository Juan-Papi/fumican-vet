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
    public function __construct(protected PurchaseNoteService $purchaseNoteService, protected PurchaseNoteDetailService $purchaseNoteDetailService, protected InventoryService $inventoryService) {}

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

    public function store(
        StorePurchaseNoteRequest $request,
        InventoryService $inventoryService,
        PurchaseNoteDetailService $purchaseNoteDetailService
    ) {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $purchaseNoteData = [
                'warehouse_id'   => $data['warehouse_id'],
                'supplier_id'    => $data['supplier_id'],
                'user_id'        => Auth::user()->id,
                'purchase_date'  => now(),
                'total_amount'   => $data['total_amount'],
            ];

            // 1) Crear la nota de compra
            $purchaseNote = $this->purchaseNoteService->createPurchaseNote($purchaseNoteData);

            // 2) Recorrer cada medicamento y crear detalle + asignar inventario
            foreach ($data['medicaments'] as $medInput) {
                // 2.1) Crear el PurchaseNoteDetail
                $purchaseNoteDetailData = [
                    'quantity'         => $medInput['quantity'],
                    'purchase_price'   => $medInput['purchase_price'],
                    'percentage'       => 0,
                    'subtotal'         => $medInput['subtotal'],
                    'purchase_note_id' => $purchaseNote->id,
                    'medicament_id'    => $medInput['id'],
                ];
                $detail = $purchaseNoteDetailService->createPurchaseNoteDetail($purchaseNoteDetailData);

                // 2.2) Crear el inventario relacionándolo con el detail recién creado
                $inventoryData = [
                    'stock'                   => $medInput['quantity'],
                    'price'                   => $medInput['purchase_price'],
                    'warehouse_id'            => $data['warehouse_id'],
                    'medicament_id'           => $medInput['id'],
                    'purchase_note_detail_id' => $detail->id,
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

    public function update(
        UpdatePurchaseNoteRequest $request,
        $id,
        InventoryService $inventoryService,
        PurchaseNoteDetailService $purchaseNoteDetailService
    ) {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            // 1) Obtener la nota de compra original
            $originalPurchaseNote = $this->purchaseNoteService->getPurchaseNoteById($id);
            $originalWarehouseId = $originalPurchaseNote->warehouse_id;

            // 2) Actualizar datos generales de la nota de compra
            $purchaseNoteData = [
                'warehouse_id' => $data['warehouse_id'],
                'supplier_id'  => $data['supplier_id'],
                'total_amount' => $data['total_amount'],
            ];
            $this->purchaseNoteService->updatePurchaseNote($id, $purchaseNoteData);

            // 3) Obtener los detalles existentes y mapearlos por ID
            $existingDetails = $purchaseNoteDetailService->getPurchaseNoteDetailsByPurchaseNoteId($id);
            // $existingDetails es una colección de PurchaseNoteDetail (con medicament incluido si definimos with('medicament'))
            $existingDetailsMap = $existingDetails->keyBy('id')->toArray();
            $existingDetailIds = array_keys($existingDetailsMap);

            // 4) Extraer los IDs que vienen del front (los medInput['id'] no son IDs de detalle,
            //    sino IDs de medicamento. Debemos asumir que, en el arreglo de front, hay un campo detail_id si es edición.)
            //    Por ejemplo, recomendamos que en el front capturemos para cada medicament algo como:
            //      { detail_id: 123, id: 5, quantity: 10, purchase_price: 20, subtotal: 200 }
            //    Si no tienen 'detail_id', entonces debemos distinguir existencia por posición u otro criterio.
            //
            //    Aquí asumiremos que el front envía un campo `detail_id` cuando es un detalle existente.
            //    De no ser así, habría que buscar de otra forma (p.ej. comparar medicament_id y demás).

            $sentDetailsRaw = $data['medicaments'];
            // Extraemos los detail_id que vienen del front:
            $sentDetailIds = array_filter(array_map(function ($d) {
                return $d['detail_id'] ?? null;
            }, $sentDetailsRaw));

            // 5) DELETED: detectar detalles que ya no existen en $sentDetailIds y borrar sus inventarios y detalles
            $detailsToDelete = array_diff($existingDetailIds, $sentDetailIds);
            foreach ($detailsToDelete as $detailId) {
                // 5.1) Borrar inventario correspondiente a ese detalle
                $inventoryService->deleteByPurchaseNoteDetailId($detailId);

                // 5.2) Borrar el detalle de la nota
                $purchaseNoteDetailService->deleteById($detailId);
            }

            // 6) Procesar cada detalle enviado desde el front
            foreach ($sentDetailsRaw as $medInput) {
                // Determinar si es EXISTENTE o NUEVO:
                if (isset($medInput['detail_id']) && in_array($medInput['detail_id'], $existingDetailIds)) {
                    // ---- EXISTENTE: actualizar PurchaseNoteDetail + su inventario ----
                    $detailId = $medInput['detail_id'];

                    // 6.1) Actualizar PurchaseNoteDetail
                    $purchaseNoteDetailData = [
                        'quantity'         => $medInput['quantity'],
                        'purchase_price'   => $medInput['purchase_price'],
                        'percentage'       => 0,
                        'subtotal'         => $medInput['subtotal'],
                        'purchase_note_id' => $id,
                        'medicament_id'    => $medInput['id'],
                    ];
                    $purchaseNoteDetailService->updatePurchaseNoteDetail($detailId, $purchaseNoteDetailData);

                    // 6.2) Actualizar inventario (si existe) o crearlo
                    $existingInventory = $inventoryService->getInventoryByPurchaseNoteDetailId($detailId);

                    $inventoryData = [
                        'stock'                   => $medInput['quantity'],
                        'price'                   => $medInput['purchase_price'],
                        'warehouse_id'            => $data['warehouse_id'],
                        'medicament_id'           => $medInput['id'],
                        'purchase_note_detail_id' => $detailId,
                    ];

                    if ($existingInventory) {
                        // Actualizar usando su ID
                        $inventoryService->updateInventory($existingInventory->id, $inventoryData);
                    } else {
                        // Crear uno nuevo (tal vez cambió de almacén o no existía antes)
                        $inventoryService->createInventory($inventoryData);
                    }
                } else {
                    // ---- NUEVO: crear PurchaseNoteDetail + inventario ----
                    $purchaseNoteDetailData = [
                        'quantity'         => $medInput['quantity'],
                        'purchase_price'   => $medInput['purchase_price'],
                        'percentage'       => 0,
                        'subtotal'         => $medInput['subtotal'],
                        'purchase_note_id' => $id,
                        'medicament_id'    => $medInput['id'],
                    ];
                    $newDetail = $purchaseNoteDetailService->createPurchaseNoteDetail($purchaseNoteDetailData);

                    $inventoryData = [
                        'stock'                   => $medInput['quantity'],
                        'price'                   => $medInput['purchase_price'],
                        'warehouse_id'            => $data['warehouse_id'],
                        'medicament_id'           => $medInput['id'],
                        'purchase_note_detail_id' => $newDetail->id,
                    ];
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // 1) Obtener todos los detalles de la nota de compra
            $purchaseNoteDetails = $this->purchaseNoteDetailService
                ->getPurchaseNoteDetailsByPurchaseNoteId($id);

            // 2) Para cada detail, borrar primero su inventario y luego el detail
            foreach ($purchaseNoteDetails as $detail) {
                // Borra el inventario asociado a este detail
                $this->inventoryService->deleteByPurchaseNoteDetailId($detail->id);

                // Borra el PurchaseNoteDetail
                $this->purchaseNoteDetailService->deleteById($detail->id);
            }

            // 3) Finalmente, borrar la propia nota de compra
            $this->purchaseNoteService->deletePurchaseNoteById($id);

            DB::commit();
            return redirect()->route('purchase.index')
                ->with('success', 'Nota de compra eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['error' => 'Error al eliminar la nota de compra: ' . $e->getMessage()]);
        }
    }
}
