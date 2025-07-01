<?php

use App\Http\Controllers\Reservations\ReserveController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/generar-qr', [ReserveController::class, 'qr'])->name('reservations.qr');
