<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

// Importamos todos los modelos que vamos a buscar
use App\Models\User;
use App\Models\Services\Pet;
use App\Models\Services\Customer;
use App\Models\Services\MedicalConsultation;
use App\Models\Sales\Supplier;
use App\Models\Sales\Medicament;
use App\Models\Sales\PurchaseNote;
use App\Models\Sales\SalesNote;
use App\Models\Sales\Category;

class GlobalSearchController extends Controller
{
    /**
     * Realiza una búsqueda global robusta en diferentes modelos.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $results = collect();

        // --- Búsqueda en Módulos de Servicios ---
        $this->findCustomers($term, $results);
        $this->findPets($term, $results);
        $this->findMedicalConsultations($term, $results);

        // --- Búsqueda en Módulos de Ventas e Inventario ---
        $this->findMedicamentsAndCategories($term, $results); // Unimos medicamentos y categorías
        $this->findSuppliers($term, $results);

        // --- Búsqueda de Notas (si el término es numérico) ---
        if (is_numeric($term)) {
            $this->findPurchaseNotes($term, $results);
            $this->findSalesNotes($term, $results);
        }

        // --- Búsqueda de Usuarios ---
        $this->findUsers($term, $results);

        // Ordenamos los resultados por título para una mejor presentación y tomamos los 10 primeros.
        return response()->json($results->sortBy('title')->values()->take(10));
    }

    private function findCustomers(string $term, Collection &$results): void
    {
        $customers = Customer::where('first_name', 'LIKE', "%{$term}%")
            ->orWhere('last_name', 'LIKE', "%{$term}%")
            ->orWhere('ci', 'LIKE', "%{$term}%")
            ->limit(5)->get();

        $results->push(...$customers->map(fn($customer) => [
            'type' => 'Cliente',
            'title' => $customer->first_name . ' ' . $customer->last_name,
            'description' => 'CI: ' . $customer->ci,
            'url' => route('customers.search', ['search_term' => $customer->first_name]),
        ]));
    }

    private function findPets(string $term, Collection &$results): void
    {
        $pets = Pet::where('name', 'LIKE', "%{$term}%")
            ->with('owner:id,first_name,last_name')
            ->limit(5)->get();

        $results->push(...$pets->map(fn($pet) => [
            'type' => 'Mascota',
            'title' => $pet->name,
            'description' => 'Propietario: ' . ($pet->owner?->first_name ?? '') . ' ' . ($pet->owner?->last_name ?? 'N/A'),
            'url' => route('medical-consultations.search', ['search_term' => $pet->name]),
        ]));
    }

    private function findMedicalConsultations(string $term, Collection &$results): void
    {
        $query = MedicalConsultation::query();
        if (is_numeric($term)) {
            $query->where('id', $term);
        } else {
            $query->where('reason', 'LIKE', "%{$term}%");
        }
        $consultations = $query->with('pet:id,name')->limit(5)->get();

        $results->push(...$consultations->map(fn($consultation) => [
            'type' => 'Consulta Médica',
            'title' => "Consulta #" . $consultation->id . ' para ' . ($consultation->pet?->name ?? 'N/A'),
            'description' => 'Motivo: ' . substr($consultation->reason, 0, 50) . '...',
            'url' => route('medical-consultations.search', ['search_term' => $consultation->id]),
        ]));
    }

    private function findMedicamentsAndCategories(string $term, Collection &$results): void
    {
        // Búsqueda de Medicamentos
        $medicaments = Medicament::where('name', 'LIKE', "%{$term}%")
            ->orWhere('manufacturer', 'LIKE', "%{$term}%")
            ->with('category:id,name') // Cargamos la categoría
            ->limit(5)->get();

        $results->push(...$medicaments->map(fn($medicament) => [
            'type' => 'Medicamento',
            'title' => $medicament->name,
            'description' => 'Categoría: ' . ($medicament->category?->name ?? 'N/A'),
            'url' => route('medicament.search', ['name' => $medicament->name]),
        ]));

        // Búsqueda de Categorías de Medicamentos
        $categories = Category::where('name', 'LIKE', "%{$term}%")->limit(3)->get();
        $results->push(...$categories->map(fn($category) => [
            'type' => 'Categoría',
            'title' => 'Categoría: ' . $category->name,
            'description' => 'Buscar todos los medicamentos de esta categoría',
            'url' => route('medicament.search', ['category_id' => $category->id]),
        ]));
    }

    private function findSuppliers(string $term, Collection &$results): void
    {
        // CORREGIDO: Eliminada la búsqueda por 'nit' que no existe. Buscamos también por email.
        $suppliers = Supplier::where('name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->limit(5)->get();

        $results->push(...$suppliers->map(fn($supplier) => [
            'type' => 'Proveedor',
            'title' => $supplier->name,
            // MEJORADO: Mostramos el email que sí existe en el modelo.
            'description' => 'Email: ' . ($supplier->email ?? 'N/A'),
            'url' => route('supplier.search', ['search_term' => $supplier->name]),
        ]));
    }

    private function findPurchaseNotes(string $term, Collection &$results): void
    {
        $notes = PurchaseNote::where('id', $term)->with('supplier:id,name')->limit(1)->get();

        $results->push(...$notes->map(fn($note) => [
            'type' => 'Nota de Compra',
            'title' => 'Compra #' . $note->id,
            'description' => 'Proveedor: ' . ($note->supplier?->name ?? 'N/A'),
            // MEJORA: La URL ahora es más simple y no causará problemas.
            'url' => route('purchase.index'),
        ]));
    }

    private function findSalesNotes(string $term, Collection &$results): void
    {
        $notes = SalesNote::where('id', $term)->with('customer:id,first_name,last_name')->limit(1)->get();

        $results->push(...$notes->map(fn($note) => [
            'type' => 'Nota de Venta',
            'title' => 'Venta #' . $note->id,
            'description' => 'Cliente: ' . ($note->customer?->first_name ?? 'N/A') . ' ' . ($note->customer?->last_name ?? ''),
            // MEJORA: La URL ahora es más simple y no causará problemas.
            'url' => route('sales-note.index'),
        ]));
    }

    private function findUsers(string $term, Collection &$results): void
    {
        $users = User::where('first_name', 'LIKE', "%{$term}%")
            ->orWhere('last_name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->limit(5)->get();

        $results->push(...$users->map(fn($user) => [
            'type' => 'Usuario',
            'title' => $user->first_name . ' ' . $user->last_name,
            'description' => 'Email: ' . $user->email,
            'url' => route('users.search', ['search_term' => $user->email]),
        ]));
    }
}
