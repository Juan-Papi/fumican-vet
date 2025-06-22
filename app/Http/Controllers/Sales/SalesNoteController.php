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
use App\Models\Sales\SalesNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use PDF;

class SalesNoteController extends Controller
{
    public function __construct(protected SalesNoteService $salesNoteService, protected InventoryService $inventoryService, protected WarehouseService $warehouseService, protected CustomerService $customerService) {}

    public function index()
    {
        $sales = $this->salesNoteService->getAllSalesNote();
        $customers = $this->customerService->getAllCustomers()->items();
        $warehouses = $this->warehouseService->getAllWarehouses()->items();

        return Inertia::render('Sales/SalesNotes/Index', [
            'sales'      => $sales,
            'customers'  => $customers,
            'warehouses' => $warehouses,
            'filters'    => null,
        ]);
    }

    public function search(Request $request)
    {
        $filters = $request->only([
            'customer_id',
            'warehouse_id',
            'date_from',
            'date_to',
            'per_page',
        ]);
        $sales = $this->salesNoteService->getFilteredSalesNotes($filters);

        $customers = $this->customerService->getAllCustomers()->items();
        $warehouses = $this->warehouseService->getAllWarehouses()->items();

        return Inertia::render('Sales/SalesNotes/Index', [
            'sales'      => $sales,
            'customers'  => $customers,
            'warehouses' => $warehouses,
            'filters'    => $filters,
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
            // Respondemos JSON para axios
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Nota de venta creada exitosamente',
                    'success' => true,
                ], 201);
            }
            // Redirección si es submit normal
            return redirect()->route('sales-note.index')->with('success', 'Nota de venta creada exitosamente');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Error al crear la nota de venta: ' . $e->getMessage(),
                    'success' => false,
                ], 500);
            }
            return redirect()->back()->withErrors(['error' => 'Error al crear la nota de venta: ' . $e->getMessage()]);
        }
    }

    public function show($id, SalesNoteDetailService $salesNoteDetailService)
    {
        $salesNote = $this->salesNoteService->getSalesNoteById($id);
        $salesNoteDetails = $salesNoteDetailService->getSalesNoteDetailsBySalesNoteId($id);

        // SIEMPRE responde JSON
        return response()->json([
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
            $salesNote = SalesNote::with('salesNoteDetails')->findOrFail($id);
            foreach ($salesNote->salesNoteDetails as $detail) {
                $this->inventoryService->restoreStockForSalesDetail($detail->id);
                $detail->delete();
            }
            $salesNote->delete();

            DB::commit();
            // RESPONDE JSON (no redirect)
            return response()->json([
                'message' => "Venta eliminada exitosamente",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar la venta',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function report(Request $request)
    {
        $filters = $request->only([
            'customer_id',
            'warehouse_id',
            'date_from',
            'date_to',
        ]);

        $sales = $this->salesNoteService->getFilteredSalesNotes($filters, false);

        $pdf = PDF::loadView('pdf.sales_notes_report', compact('sales', 'filters'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('reporte_ventas_' . now()->format('Ymd') . '.pdf');
    }
}
