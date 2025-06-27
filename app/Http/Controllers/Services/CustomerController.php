<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreCustomerRequest;
use App\Http\Requests\Services\UpdateCustomerRequest;
use App\Models\Services\Customer;
use Illuminate\Http\JsonResponse;
use App\Services\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('Services/Customers/Index', [
            'customers' => $this->customerService->getAllCustomers(),
            'filters' => [],
        ]);
    }

    public function search(Request $request)
    {
        $filters = $request->only('search_term');

        // Si la petición es para autocompletar, devuelve JSON
        if ($request->has('autocomplete')) {
            $customers = $this->customerService->autocompleteSearch($request->search_term);
            return response()->json($customers);
        }

        // Si no, es para filtrar la tabla, devuelve la vista
        return Inertia::render('Services/Customers/Index', [
            'customers' => $this->customerService->search($filters),
            'filters' => $filters,
        ]);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return response()->json(['message' => 'Cliente registrado correctamente.', 'customer' => $customer], 201);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $this->customerService->update($request->validated(), $customer->id);
        return response()->json(['message' => 'Cliente actualizado correctamente.']);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->customerService->delete($customer->id);
        return response()->json(['message' => 'Cliente eliminado correctamente.']);
    }
}
