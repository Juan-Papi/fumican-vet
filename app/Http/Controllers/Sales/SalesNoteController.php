<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesNoteDetailService;
use App\Services\Sales\SalesNoteService;
use App\Services\Sales\WarehouseService;
use App\Services\Sales\MedicamentService;
use App\Services\Sales\InventoryService;
use App\Services\Services\CustomerService;
use App\Http\Requests\Sales\StoreSalesNoteRequest;
use App\Http\Requests\Sales\UpdateSalesNoteRequest;
use App\Models\Sales\Inventory;
use App\Models\Sales\SalesNote;
use App\Models\Sales\SalesNoteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PDF;

class SalesNoteController extends Controller
{
    public function __construct(protected SalesNoteService $salesNoteService, protected InventoryService $inventoryService) {}

    public function index()
    {
        $sales = $this->salesNoteService->getAllSalesNote();
        return Inertia::render('Sales/SalesNotes/Index', [
            'sales' => $sales,
        ]);
    }

    public function create(WarehouseService $warehouseService, MedicamentService $medicamentService, CustomerService $customerService)
    {
        $customers = $customerService->getAllCustomers()->items();
        $warehouses = $warehouseService->getAllWarehouses()->items();
        $medicamentsList = $medicamentService->getAllMedicaments()->items();

        return Inertia::render('Sales/SalesNotes/Form', [
            'formAction' => 'create',
            'customers' => $customers,
            'warehouses' => $warehouses,
            'medicamentsList' => $medicamentsList,
        ]);
    }

    public function store(StoreSalesNoteRequest $request, SalesNoteService $salesNoteService)
    {
        $data = $request->validated();
        $data['sale_date'] = now();
        $data['user_id'] = Auth::id();
        try {
            $salesNoteService->createSalesNoteWithInventory($data);
            return redirect()->route('sales-note.index')->with('success', 'Nota de venta creada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al crear la nota de venta: ' . $e->getMessage()]);
        }
    }

    public function show($id, SalesNoteDetailService $salesNoteDetailService)
    {
        $salesNote = $this->salesNoteService->getSalesNoteById($id);
        $salesNoteDetails = $salesNoteDetailService->getSalesNoteDetailsBySalesNoteId($id);

        return Inertia::render('Sales/SalesNotes/Show', [
            'salesNote' => $salesNote,
            'salesNoteDetails' => $salesNoteDetails,
        ]);
    }

    public function edit(
        int $id,
        WarehouseService $warehouseService,
        MedicamentService $medicamentService,
        CustomerService $customerService,
        SalesNoteDetailService $detailService
    ) {
        $salesNote        = $this->salesNoteService->getSalesNoteById($id);
        $salesNoteDetails = $detailService->getSalesNoteDetailsBySalesNoteId($id);

        $customers     = $customerService->getAllCustomers()->items();
        $warehouses    = $warehouseService->getAllWarehouses()->items();
        $medicaments   = $medicamentService->getAllMedicaments()->items();

        return Inertia::render('Sales/SalesNotes/FormEdit', [
            'salesNote'         => $salesNote,
            'salesNoteDetails'  => $salesNoteDetails,
            'customers'         => $customers,
            'warehouses'        => $warehouses,
            'medicamentsList'   => $medicaments,
        ]);
    }


    public function update(UpdateSalesNoteRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $this->salesNoteService->updateSalesNoteWithInventory($id, $data);
            return redirect()->route('sales-note.index')->with('success', 'Venta actualizada');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al actualizar la nota de venta: ' . $e->getMessage()]);
        }
    }

    public function generatePdf($id, SalesNoteDetailService $salesNoteDetailService)
    {
        $salesNote = $this->salesNoteService->getSalesNoteById($id);
        $salesNoteDetails = $salesNoteDetailService->getSalesNoteDetailsBySalesNoteId($id);

        $pdf = PDF::loadView('pdf.sales_note', compact('salesNote', 'salesNoteDetails'));
        return $pdf->stream('nota_de_venta.pdf');
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $salesNote = SalesNote::findOrFail($id);

            foreach ($salesNote->details as $detail) {
                // Restaurar el inventario (sumar las unidades vendidas)
                $inventory = Inventory::where('medicament_id', $detail->medicament_id)
                    ->where('warehouse_id', $salesNote->warehouse_id)
                    ->first();

                if ($inventory) {
                    $this->inventoryService->restoreInventoryStock($inventory->id, $detail->quantity);
                }

                // Eliminar los detalles de la venta
                $detail->delete();
            }

            // Eliminar la nota de venta
            $salesNote->delete();

            DB::commit();
            return redirect()->route('sales-note.index')->with('success', 'Venta eliminada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Error al eliminar la venta: ' . $e->getMessage()]);
        }
    }
}
