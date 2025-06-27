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
        $consultationsByMonth = MedicalConsultation::select(DB::raw('count(id) as count'), DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'))->where('created_at', '>=', now()->subYear())->groupBy('month')->orderBy('month', 'asc')->get()->keyBy('month');
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
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month')
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
        $salesByMonth = SalesNote::select(DB::raw('SUM(total_amount) as total'), DB::raw('DATE_FORMAT(sale_date, "%Y-%m") as month'))->where('sale_date', '>=', now()->subMonths(6))->groupBy('month')->orderBy('month', 'asc')->get()->keyBy('month');
        $purchasesByMonth = PurchaseNote::select(DB::raw('SUM(total_amount) as total'), DB::raw('DATE_FORMAT(purchase_date, "%Y-%m") as month'))->where('purchase_date', '>=', now()->subMonths(6))->groupBy('month')->orderBy('month', 'asc')->get()->keyBy('month');

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

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }
}
