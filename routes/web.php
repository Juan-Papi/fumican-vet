<?php

use App\Http\Controllers\Services\BreedController;
use App\Http\Controllers\Services\CustomerController;
use App\Http\Controllers\Services\MedicalConsultationController;
use App\Http\Controllers\Services\PetController;
use App\Http\Controllers\Services\SpecieController;
use App\Http\Controllers\Users\RoleController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Sales\SupplierController;
use App\Http\Controllers\Sales\CategoryController;
use App\Http\Controllers\Sales\MedicamentController;
use App\Http\Controllers\Sales\WarehouseController;
use App\Http\Controllers\Sales\PurchaseNoteController;
use App\Http\Controllers\Sales\SalesNoteController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    Route::group(['prefix' => 'users'], function () {
        Route::resource('/users', UserController::class);
        Route::resource('/roles', RoleController::class);
    });
    Route::group(['prefix' => 'services'], function () {
        Route::get('/customers-search', [CustomerController::class, 'search'])->name('customers.search');
        Route::resource('/customers', CustomerController::class);

        Route::resource('/medical-consultations', MedicalConsultationController::class);

        Route::get('/species-search', [SpecieController::class, 'search'])->name('species.search');
        Route::resource('/species', SpecieController::class);

        Route::get('/breeds-search', [BreedController::class, 'search'])->name('breeds.search');
        Route::resource('/breeds', BreedController::class);

        Route::post('/pets-prepare-store-data', [PetController::class, 'prepareStoreData'])->name('pets.prepareStoreData');
        Route::get('/pets-search', [PetController::class, 'search'])->name('pets.search');
        Route::resource('/pets', PetController::class);
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::group(['prefix' => 'suppliers'], function () {
            Route::get('/', [SupplierController::class, 'index'])->name('supplier.index');
            Route::get('create', [SupplierController::class, 'create'])->name('supplier.create');
            Route::post('/', [SupplierController::class, 'store'])->name('supplier.store');
            Route::get('{id}/edit', [SupplierController::class, 'edit'])->name('supplier.edit');
            Route::put('{id}', [SupplierController::class, 'update'])->name('supplier.update');
            Route::get('search', [SupplierController::class, 'search'])->name('supplier.search');
        });

        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('category.index');
            Route::get('create', [CategoryController::class, 'create'])->name('category.create');
            Route::post('/', [CategoryController::class, 'store'])->name('category.store');
            Route::get('{id}/edit', [CategoryController::class, 'edit'])->name('category.edit');
            Route::put('{id}', [CategoryController::class, 'update'])->name('category.update');
            Route::get('search', [CategoryController::class, 'search'])->name('supplier.search');
        });

        Route::group(['prefix' => 'medicaments'], function () {
            Route::get('/', [MedicamentController::class, 'index'])->name('medicament.index');
            Route::get('create', [MedicamentController::class, 'create'])->name('medicament.create');
            Route::post('/', [MedicamentController::class, 'store'])->name('medicament.store');
            Route::get('{id}/edit', [MedicamentController::class, 'edit'])->name('medicament.edit');
            Route::put('{id}', [MedicamentController::class, 'update'])->name('medicament.update');
            Route::get('search', [MedicamentController::class, 'search'])->name('medicament.search');
        });

        Route::group(['prefix' => 'warehouses'], function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('warehouse.index');
            Route::get('create', [WarehouseController::class, 'create'])->name('warehouse.create');
            Route::post('/', [WarehouseController::class, 'store'])->name('warehouse.store');
            Route::get('{id}/edit', [WarehouseController::class, 'edit'])->name('warehouse.edit');
            Route::put('{id}', [WarehouseController::class, 'update'])->name('warehouse.update');
            Route::get('search', [WarehouseController::class, 'search'])->name('warehouse.search');
            Route::get('{id}', [WarehouseController::class, 'show'])->name('warehouse.show');
            Route::get('{warehouseId}/medicament/{medicamentId}/inventory', [WarehouseController::class, 'showInventoryMedicament'])
                ->name('warehouse.medicament.inventory');
        });

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', [PurchaseNoteController::class, 'index'])->name('purchase.index');
            Route::get('create', [PurchaseNoteController::class, 'create'])->name('purchase.create');
            Route::post('/', [PurchaseNoteController::class, 'store'])->name('purchase.store');
            Route::get('{id}/edit', [PurchaseNoteController::class, 'edit'])->name('purchase.edit');
            Route::put('{id}', [PurchaseNoteController::class, 'update'])->name('purchase.update');
            Route::get('{id}', [PurchaseNoteController::class, 'show'])->name('purchase.show');
            Route::get('purchases/{id}/pdf', [PurchaseNoteController::class, 'generatePdf'])->name('purchase.pdf');
        });

        Route::group(['prefix' => 'sales-note'], function () {
            Route::get('/', [SalesNoteController::class, 'index'])->name('sales-note.index');
            Route::get('create', [SalesNoteController::class, 'create'])->name('sales-note.create');
            Route::post('/', [SalesNoteController::class, 'store'])->name('sales-note.store');
            Route::get('{id}/edit', [SalesNoteController::class, 'edit'])->name('sales-note.edit');
            Route::put('{id}', [SalesNoteController::class, 'update'])->name('sales-note.update');
            Route::get('{id}', [SalesNoteController::class, 'show'])->name('sales-note.show');
            Route::get('sales-note/{id}/pdf', [SalesNoteController::class, 'generatePdf'])->name('sales-note.pdf');
        });
    });
});
