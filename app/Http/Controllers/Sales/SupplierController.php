<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SupplierService;
use App\Http\Requests\Sales\StoreSupplierRequest;
use Illuminate\Http\Request;

use Inertia\Inertia;

class SupplierController extends Controller
{

    public function __construct(protected SupplierService $supplierService) {}

    public function index()
    {
        $suppliers = $this->supplierService->getAllSuppliers();
        return Inertia::render('Sales/Suppliers/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function create()
    {
        return Inertia::render(
            'Sales/Suppliers/Form',
            ['formAction' => 'create']
        );
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->supplierService->createSupplier($request->validated());
        return redirect()->route('supplier.index');
    }

}
