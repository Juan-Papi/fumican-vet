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
use App\Http\Controllers\Calidad\CalidadController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Reservations\ReserveController;
use App\Http\Controllers\GlobalSearchController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::group(['prefix' => 'users'], function () {
        Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('search', [UserController::class, 'search'])->name('search');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::put('{user}', [UserController::class, 'update'])->name('update');
            Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        });
        Route::resource('/roles', RoleController::class);
    });
    Route::group(['prefix' => 'services'], function () {

        Route::group(['prefix' => 'customers', 'as' => 'customers.'], function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('search', [CustomerController::class, 'search'])->name('search'); // Esta será SOLO para filtrar la tabla
            Route::get('autocomplete', [CustomerController::class, 'autocomplete'])->name('autocomplete'); // <-- NUEVA RUTA
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::put('{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        Route::group(['prefix' => 'medical-consultations', 'as' => 'medical-consultations.'], function () {
            Route::get('/', [MedicalConsultationController::class, 'index'])->name('index');
            Route::post('/', [MedicalConsultationController::class, 'store'])->name('store');
            Route::put('{id}', [MedicalConsultationController::class, 'update'])->name('update');
            Route::delete('{id}', [MedicalConsultationController::class, 'destroy'])->name('destroy');
            Route::get('search', [MedicalConsultationController::class, 'search'])->name('search');
            Route::get('report', [MedicalConsultationController::class, 'generateConsultationsReport'])->name('report');
            Route::get('pets/{pet}/history-report', [MedicalConsultationController::class, 'generatePetHistoryReport'])->name('pet-history-report');
        });

        Route::get('/species-search', [SpecieController::class, 'search'])->name('species.search');
        Route::resource('/species', SpecieController::class);

        Route::get('/breeds-search', [BreedController::class, 'search'])->name('breeds.search');
        Route::resource('/breeds', BreedController::class);

        Route::group(['prefix' => 'pets', 'as' => 'pets.'], function () {
            Route::get('/', [PetController::class, 'index'])->name('index');
            Route::get('search', [PetController::class, 'search'])->name('search'); // Para filtrar la lista principal
            Route::post('/', [PetController::class, 'store'])->name('store');
            Route::put('{pet}', [PetController::class, 'update'])->name('update');
            Route::delete('{pet}', [PetController::class, 'destroy'])->name('destroy');

            // Rutas auxiliares para la lógica del formulario en el modal
            Route::get('autocomplete', [PetController::class, 'autocomplete'])->name('autocomplete'); // Búsqueda para otras partes del sistema
            Route::post('prepare-data', [PetController::class, 'prepareStoreData'])->name('prepare-data');
        });
    });

    Route::group(['prefix' => 'sales'], function () {
        Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('supplier.index');
            Route::post('/', [SupplierController::class, 'store'])->name('supplier.store');
            Route::put('{id}', [SupplierController::class, 'update'])->name('supplier.update');
            Route::delete('{id}', [SupplierController::class, 'destroy'])->name('supplier.destroy');
            Route::get('search', [SupplierController::class, 'search'])->name('supplier.search');
        });

        Route::group(['prefix' => 'categories'], function () {
            Route::get('/', [CategoryController::class, 'index'])->name('category.index');
            Route::post('/', [CategoryController::class, 'store'])->name('category.store');
            Route::put('{id}', [CategoryController::class, 'update'])->name('category.update');
            Route::delete('{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
            Route::get('search', [CategoryController::class, 'search'])->name('category.search');
        });

        Route::group(['prefix' => 'medicaments'], function () {
            Route::get('/', [MedicamentController::class, 'index'])->name('medicament.index');
            Route::post('/', [MedicamentController::class, 'store'])->name('medicament.store');
            Route::get('{id}/edit', [MedicamentController::class, 'edit'])->name('medicament.edit');
            Route::put('{id}', [MedicamentController::class, 'update'])->name('medicament.update');
            Route::delete('{id}', [MedicamentController::class, 'destroy'])->name('medicament.destroy');
            Route::get('search', [MedicamentController::class, 'search'])->name('medicament.search');
            Route::get('medicaments/report', [MedicamentController::class, 'generatePdf'])
                ->name('medicament.report');
        });

        Route::group(['prefix' => 'warehouses'], function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('warehouse.index');
            Route::post('/', [WarehouseController::class, 'store'])->name('warehouse.store');
            Route::put('{id}', [WarehouseController::class, 'update'])->name('warehouse.update');
            Route::post('{id}', [WarehouseController::class, 'destroy'])->name('warehouse.destroy');
            Route::get('search', [WarehouseController::class, 'search'])->name('warehouse.search');
            Route::get('{id}', [WarehouseController::class, 'show'])->name('warehouse.show');
            Route::get('{warehouseId}/medicament/{medicamentId}/inventory', [WarehouseController::class, 'showInventoryMedicament'])
                ->name('warehouse.medicament.inventory');
            Route::post(
                '{warehouseId}/medicament/{medicamentId}/inventory',
                [WarehouseController::class, 'storeInventory']
            )->name('warehouse.medicament.inventory.store');
            Route::put(
                '{warehouseId}/medicament/{medicamentId}/inventory/{inventoryId}',
                [WarehouseController::class, 'updateInventory']
            )->name('warehouse.medicament.inventory.update');
            Route::delete(
                '{warehouseId}/medicament/{medicamentId}/inventory/{inventoryId}',
                [WarehouseController::class, 'destroyInventory']
            )->name('warehouse.medicament.inventory.destroy');
        });

        Route::group(['prefix' => 'purchases'], function () {
            Route::get('/', [PurchaseNoteController::class, 'index'])->name('purchase.index');
            Route::get('create', [PurchaseNoteController::class, 'create'])->name('purchase.create');
            Route::post('/', [PurchaseNoteController::class, 'store'])->name('purchase.store');
            Route::get('report', [PurchaseNoteController::class, 'report'])->name('purchase.report'); // <-- PON ESTA ANTES
            Route::get('purchases/{id}/pdf', [PurchaseNoteController::class, 'generatePdf'])->name('purchase.pdf');
            Route::get('{id}/edit', [PurchaseNoteController::class, 'edit'])->name('purchase.edit');
            Route::put('{id}', [PurchaseNoteController::class, 'update'])->name('purchase.update');
            Route::get('{id}', [PurchaseNoteController::class, 'show'])->name('purchase.show');
            Route::delete('{id}', [PurchaseNoteController::class, 'destroy'])->name('purchase.destroy');
            Route::get('/purchase/search', [PurchaseNoteController::class, 'search'])->name('purchase.search');
        });

        Route::group(['prefix' => 'sales-note'], function () {
            Route::get('/', [SalesNoteController::class, 'index'])->name('sales-note.index'); // /sales-note
            Route::get('create', [SalesNoteController::class, 'create'])->name('sales-note.create');
            Route::post('/', [SalesNoteController::class, 'store'])->name('sales-note.store');
            Route::get('search', [SalesNoteController::class, 'search'])->name('sales-note.search');
            Route::get('report', [SalesNoteController::class, 'report'])->name('sales-note.report');
            Route::get('{id}/edit', [SalesNoteController::class, 'edit'])->name('sales-note.edit');
            Route::put('{id}', [SalesNoteController::class, 'update'])->name('sales-note.update');
            Route::get('{id}', [SalesNoteController::class, 'show'])->name('sales-note.show');
            Route::get('{id}/pdf', [SalesNoteController::class, 'generatePdf'])->name('sales-note.pdf');
            Route::delete('{id}', [SalesNoteController::class, 'destroy'])->name('sales-note.destroy');
        });


    });
     // CALIDAD
    Route::get('/calidad/prompt', [CalidadController::class, 'index'])->name('calidad.prompt.index');
    Route::post('/calidad/generate', [CalidadController::class, 'generate'])->name('calidad.prompt.generate');
});

Route::post('/reserve-pdf', [ReserveController::class, 'reservePdf'])->name('reservations.reserve-pdf');
Route::get('/global-search', [GlobalSearchController::class, 'search'])->name('global.search');
