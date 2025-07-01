<?php

namespace App\Http\Controllers;

use App\Models\Services\Customer;
use App\Models\Services\MedicalConsultation;
use App\Models\Services\Pet;
use App\Models\Sales\PurchaseNote;
use App\Models\Sales\SalesNote;
use App\Models\Sales\SalesNoteDetail;
use App\Models\Sales\Inventory;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Detectar el driver de la base de datos actual para usar la sintaxis correcta.
        $dbDriver = DB::connection()->getDriverName();

        // --- GRÁFICOS DE SERVICIOS ---

        // 1. Consultas por Mes (Lógica unificada para MySQL y PostgreSQL)
        $monthExpressionConsultations = ($dbDriver === 'mysql')
            ? 'DATE_FORMAT(created_at, "%Y-%m")'
            : "TO_CHAR(created_at, 'YYYY-MM')";

        $consultationsByMonth = MedicalConsultation::select(
            DB::raw('count(id) as count'),
            DB::raw("{$monthExpressionConsultations} as month")
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy(DB::raw($monthExpressionConsultations)) // Agrupar por la expresión funciona en ambos
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

        // 2. Mascotas por Especie (Esta consulta ya era compatible)
        $petsBySpecies = Pet::join('breeds', 'pets.breed_id', '=', 'breeds.id')
            ->join('species', 'breeds.specie_id', '=', 'species.id')
            ->select('species.name', DB::raw('count(pets.id) as count'))
            ->groupBy('species.name')
            ->pluck('count', 'name');

        // 3. Nuevos Clientes (últimos 6 meses) - (Lógica unificada)
        $monthExpressionCustomers = ($dbDriver === 'mysql')
            ? 'DATE_FORMAT(created_at, "%Y-%m")'
            : "TO_CHAR(created_at, 'YYYY-MM')";

        $newCustomersByMonth = Customer::select(
            DB::raw('count(id) as count'),
            DB::raw("{$monthExpressionCustomers} as month")
        )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw($monthExpressionCustomers))
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

        // 4. Ventas y Compras (últimos 6 meses) - (Lógica unificada)
        $dateFilter = ($dbDriver === 'mysql') ? now()->subMonths(6) : now()->subMonths(6)->format('Y-m-d');

        // Lógica para Ventas
        $salesMonthExpression = ($dbDriver === 'mysql')
            ? 'DATE_FORMAT(sale_date, "%Y-%m")'
            : "TO_CHAR(CAST(sale_date AS DATE), 'YYYY-MM')";
        $salesTotalExpression = ($dbDriver === 'mysql')
            ? 'SUM(total_amount)'
            : 'SUM(CAST(total_amount AS DECIMAL(10,2)))';

        $salesByMonth = SalesNote::select(
            DB::raw("{$salesTotalExpression} as total"),
            DB::raw("{$salesMonthExpression} as month")
        )
            ->where('sale_date', '>=', $dateFilter)
            ->groupBy(DB::raw($salesMonthExpression))
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        // Lógica para Compras
        $purchasesMonthExpression = ($dbDriver === 'mysql')
            ? 'DATE_FORMAT(purchase_date, "%Y-%m")'
            : "TO_CHAR(CAST(purchase_date AS DATE), 'YYYY-MM')";
        $purchasesTotalExpression = ($dbDriver === 'mysql')
            ? 'SUM(total_amount)'
            : 'SUM(CAST(total_amount AS DECIMAL(10,2)))';

        $purchasesByMonth = PurchaseNote::select(
            DB::raw("{$purchasesTotalExpression} as total"),
            DB::raw("{$purchasesMonthExpression} as month")
        )
            ->where('purchase_date', '>=', $dateFilter)
            ->groupBy(DB::raw($purchasesMonthExpression))
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
            $salesData[] = floatval($salesByMonth->get($monthKey)->total ?? 0); // Usar floatval por el cast a DECIMAL
            $purchasesData[] = floatval($purchasesByMonth->get($monthKey)->total ?? 0); // Usar floatval
        }

        // 5. Top 5 Medicamentos más vendidos (cantidad) - (Ya era compatible)
        $topMedicaments = SalesNoteDetail::select('medicament_id', DB::raw('SUM(quantity) as total_quantity'))
            ->with('medicament:id,name')
            ->groupBy('medicament_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // 6. Valor del Inventario por Almacén - (Ya era compatible)
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

        // Se mantiene el contador de visitas de tu versión de PostgreSQL
        $visitCount = class_exists(Visit::class) ? Visit::getCount() : 0;

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'visitCount' => $visitCount
        ]);
    }
}
