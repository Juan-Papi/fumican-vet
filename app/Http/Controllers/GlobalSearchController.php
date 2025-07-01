<?php

namespace App\Http\Controllers;

use App\Models\Services\Customer;
use App\Models\Services\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GlobalSearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('term', '');

        // Si el término es muy corto, no buscamos nada.
        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = collect();

        // --- Búsqueda de Mascotas ---
        $pets = Pet::where('name', 'LIKE', "%{$term}%")
            ->with('owner:id,first_name,last_name') // Carga el dueño para mostrar su nombre
            ->limit(5) // Limita el número de resultados
            ->get();

        $results = $results->merge($pets->map(function ($pet) {
            return [
                'type' => 'Mascota',
                'title' => $pet->name,
                // Creamos una URL que lleva a la vista de consultas y filtra por la mascota
                'url' => route('medical-consultations.search', ['search_term' => $pet->name]),
                'description' => 'Propietario: ' . ($pet->owner->first_name ?? '') . ' ' . ($pet->owner->last_name ?? ''),
            ];
        }));

        // --- Búsqueda de Clientes/Propietarios ---
        $customers = Customer::where('first_name', 'LIKE', "%{$term}%")
            ->orWhere('last_name', 'LIKE', "%{$term}%")
            ->orWhere('ci', 'LIKE', "%{$term}%")
            ->limit(5)
            ->get();

        $results = $results->merge($customers->map(function ($customer) {
            return [
                'type' => 'Cliente',
                'title' => $customer->first_name . ' ' . $customer->last_name,
                // Creamos una URL que lleva a la vista de mascotas y filtra por el cliente
                'url' => route('pets.search', ['search_term' => $customer->first_name]),
                'description' => 'CI: ' . $customer->ci,
            ];
        }));

        // Puedes añadir más búsquedas aquí (ej. por motivo de consulta) en el futuro.

        return response()->json($results);
    }
}
