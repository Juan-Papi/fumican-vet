<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservations\ReserveRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReserveController extends Controller
{
    public function reservePdf(Request $request)
    {   
        
        // $request->validate([
        //     'nombre' => 'required|string|max:255',
        //     'telefono' => 'required|string|max:20',
        //     'correo' => 'required|email|max:255',
        //     'mascota' => 'required|string|max:255',
        //     'servicio' => 'required|string|max:255',
        //     'fecha' => 'required|date',
        //     'horario' => 'required|string|max:50',
        //     'comentario' => 'nullable|string|max:500',
        // ]);

        $data = [
            'name' => 'Juan Pérez',
            'phone' => '555-1234567',
            'email' => 'juan@example.com',
            'petName' => 'Firulais',
            'service' => 'Consulta',
            'date' => '2025-07-05',
            'timeSlot' => '10:00',
            'comment' => 'Ninguno',
        ];
        // $data = $request->all();

        $pdf = PDF::loadView('pdf.reserve', compact('data'));
        return $pdf->download('reserva.pdf');

    }
}
