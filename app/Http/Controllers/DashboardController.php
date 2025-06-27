<?php

namespace App\Http\Controllers;

use App\Models\Services\Customer;
use App\Models\Services\MedicalConsultation;
use App\Models\Services\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Consultas por Mes (últimos 12 meses)
        $consultationsByMonth = MedicalConsultation::select(
            DB::raw('count(id) as count'),
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month')
        )
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->keyBy('month');

        $consultationMonths = [];
        $consultationData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $consultationMonths[] = $month->isoFormat('MMM YYYY');
            $consultationData[] = $consultationsByMonth->get($monthKey)->count ?? 0;
        }

        // 2. Mascotas por Especie
        $petsBySpecies = Pet::join('breeds', 'pets.breed_id', '=', 'breeds.id')
            ->join('species', 'breeds.specie_id', '=', 'species.id')
            ->select('species.name', DB::raw('count(pets.id) as count'))
            ->groupBy('species.name')
            ->pluck('count', 'name');

        // 3. Nuevos Clientes (últimos 6 meses)
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
            $customerMonths[] = $month->isoFormat('MMM YYYY');
            $customerData[] = $newCustomersByMonth->get($monthKey)->count ?? 0;
        }


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
            ]
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }
}
