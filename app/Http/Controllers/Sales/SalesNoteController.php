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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PDF;

class SalesNoteController extends Controller
{
    public function __construct(protected SalesNoteService $salesNoteService) {}

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

    public function store(StoreSalesNoteRequest $request, InventoryService $inventoryService, SalesNoteDetailService $salesNoteDetailService)
    {
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $salesNoteData = [
                'warehouse_id' => $data['warehouse_id'],
                'customer_id' => $data['customer_id'],
                'user_id' => Auth::user()->id,
                'sale_date' => now(),
                'total_amount' => $data['total_amount'],
            ];

            $salesNote = $this->salesNoteService->createSalesNoteDetail($salesNoteData);

            foreach ($data['medicaments'] as $medicament) {
                $totalStock = $inventoryService->getTotalStockByMedicamentAndWarehouse($medicament['id'], $data['warehouse_id']);
                if ($totalStock < $medicament['quantity']) {
                    throw new \Exception("Not enough stock for medicament ID: {$medicament['id']}");
                }

                $remainingQuantity = $medicament['quantity'];
                $inventories = $inventoryService->getInventoriesByWarehoseAndMedicament($data['warehouse_id'], $medicament['id']);

                foreach ($inventories as $inventory) {
                    if ($remainingQuantity <= 0) break;

                    $deductQuantity = min($inventory->stock, $remainingQuantity);
                    $inventory->stock -= $deductQuantity;
                    $inventory->save();

                    $remainingQuantity -= $deductQuantity;
                }

                $salesNoteDetailData = [
                    'quantity' => $medicament['quantity'],
                    'sale_price' => $medicament['sale_price'],
                    'subtotal' => $medicament['subtotal'],
                    'sales_note_id' => $salesNote->id,
                    'medicament_id' => $medicament['id'],
                ];
                $salesNoteDetailService->createPurchaseNoteDetail($salesNoteDetailData);
            }

            DB::commit();
            return redirect()->route('sales-note.index')->with('success', 'Nota de venta creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
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

    public function generatePdf($id, SalesNoteDetailService $salesNoteDetailService)
    {
        $salesNote = $this->salesNoteService->getSalesNoteById($id);
        $salesNoteDetails = $salesNoteDetailService->getSalesNoteDetailsBySalesNoteId($id);

        $pdf = PDF::loadView('pdf.sales_note', compact('salesNote', 'salesNoteDetails'));
        return $pdf->stream('nota_de_venta.pdf');
    }
}
