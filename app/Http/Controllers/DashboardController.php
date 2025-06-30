<?php

namespace App\Http\Controllers;

use App\Models\Services\Customer;
use App\Models\Services\MedicalConsultation;
use App\Models\Services\Pet;
use App\Models\Sales\PurchaseNote;
use App\Models\Sales\SalesNote;
use App\Models\Sales\SalesNoteDetail;
use App\Models\Sales\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // --- GRÁFICOS DE SERVICIOS ---
        // 1. Consultas por Mes
        // NOTA: Modificado para PostgreSQL - Se cambió DATE_FORMAT por TO_CHAR
        $consultationsByMonth = MedicalConsultation::select(
            DB::raw('count(id) as count'), 
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month")
        )
        ->where('created_at', '>=', now()->subYear())
        ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
        ->orderBy('month', 'asc')
        ->get()
        ->keyBy('month');
        $consultationMonths = [];
        $consultationData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $consultationMonths[] = $month->isoFormat('MMM YY');
            $consultationData[] = $consultationsByMonth->get($monthKey)->count ?? 0;
        }

        // 2. Mascotas por Especie
        $petsBySpecies = Pet::join('breeds', 'pets.breed_id', '=', 'breeds.id')->join('species', 'breeds.specie_id', '=', 'species.id')->select('species.name', DB::raw('count(pets.id) as count'))->groupBy('species.name')->pluck('count', 'name');

        // 3. Nuevos Clientes (últimos 6 meses) - RE-AÑADIDO
        $newCustomersByMonth = Customer::select(
            DB::raw('count(id) as count'),
            DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month")
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        $customerMonths = [];
        $customerData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $customerMonths[] = $month->isoFormat('MMM YY');
            $customerData[] = $newCustomersByMonth->get($monthKey)->count ?? 0;
        }

        // --- GRÁFICOS DE VENTAS Y COMPRAS ---

        // 4. Ventas y Compras (últimos 6 meses)
        // NOTA: Modificado para PostgreSQL
        // - Se cambió DATE_FORMAT por TO_CHAR
        // - Se añadió CAST a DATE para asegurar el tipo de dato
        // - Se formateó la fecha en la cláusula where para coincidir con el formato de PostgreSQL
        $salesByMonth = SalesNote::select(
                DB::raw('SUM(CAST(total_amount AS DECIMAL(10,2))) as total'), 
                DB::raw("TO_CHAR(CAST(sale_date AS DATE), 'YYYY-MM') as month")
            )
            ->where('sale_date', '>=', now()->subMonths(6)->format('Y-m-d'))
            ->groupBy(DB::raw("TO_CHAR(CAST(sale_date AS DATE), 'YYYY-MM')"))
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');
            
        // Mismas modificaciones para las compras
        $purchasesByMonth = PurchaseNote::select(
                DB::raw('SUM(CAST(total_amount AS DECIMAL(10,2))) as total'), 
                DB::raw("TO_CHAR(CAST(purchase_date AS DATE), 'YYYY-MM') as month")
            )
            ->where('purchase_date', '>=', now()->subMonths(6)->format('Y-m-d'))
            ->groupBy(DB::raw("TO_CHAR(CAST(purchase_date AS DATE), 'YYYY-MM')"))
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        $salesMonths = [];
        $salesData = [];
        $purchasesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $salesMonths[] = $month->isoFormat('MMM YY');
            $salesData[] = $salesByMonth->get($monthKey)->total ?? 0;
            $purchasesData[] = $purchasesByMonth->get($monthKey)->total ?? 0;
        }

        // 5. Top 5 Medicamentos más vendidos (cantidad)
        $topMedicaments = SalesNoteDetail::select('medicament_id', DB::raw('SUM(quantity) as total_quantity'))
            ->with('medicament:id,name')
            ->groupBy('medicament_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // 6. Valor del Inventario por Almacén
        $inventoryValueByWarehouse = Inventory::select('warehouse_id', DB::raw('SUM(stock * price) as total_value'))
            ->with('warehouse:id,name')
            ->groupBy('warehouse_id')
            ->get();


        $stats = [
            'consultations' => [
                'labels' => $consultationMonths,
                'data' => $consultationData,
            ],
            'species' => [
                'labels' => $petsBySpecies->keys(),
                'data' => $petsBySpecies->values(),
            ],
            'newCustomers' => [
                'labels' => $customerMonths,
                'data' => $customerData,
            ],
            'salesVsPurchases' => [
                'labels' => $salesMonths,
                'sales' => $salesData,
                'purchases' => $purchasesData,
            ],
            'topMedicaments' => [
                'labels' => $topMedicaments->pluck('medicament.name'),
                'data' => $topMedicaments->pluck('total_quantity'),
            ],
            'inventoryValue' => [
                'labels' => $inventoryValueByWarehouse->pluck('warehouse.name'),
                'data' => $inventoryValueByWarehouse->pluck('total_value'),
            ]
        ];

        // Obtener el contador de visitas
        $visitCount = \App\Models\Visit::getCount();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'visitCount' => $visitCount
        ]);
    }
}
