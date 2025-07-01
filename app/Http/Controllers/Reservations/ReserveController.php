<?php

namespace App\Http\Controllers\Reservations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservations\ReserveRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

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

    public function qr(Request $request)
    {
        try {
            Log::info('generando qr', [
                'request' => 'generacion'
            ]);
            // Parámetros necesarios para generar el QR
            $lcComerceID = "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c";
            $lnMoneda = 2;
            $lnTelefono = $request->telefono;
            $lcNombreUsuario = $request->razon_social;
            $lnCiNit = $request->nit;
            $lcNroPago = "test-grupo17sa" . rand(100000, 999999);
            $lnMontoClienteEmpresa = $request->total;
            $lcCorreo = $request->correo;
            $lcUrlCallBack = "https://mail.tecnoweb.org.bo/inf513/grupo17sa";
            $lcUrlReturn = "http://fumican-vet.test/";

            $laPedidoDetalle = [
                [
                    'Serial' => '123',
                    'Producto' => 'Producto ejemplo',
                    'Cantidad' => 1,
                    'Precio' => $lnMontoClienteEmpresa,
                    'Descuento' => 0,
                    'Total' => $lnMontoClienteEmpresa,
                ]
            ];

            // API para generar QR
            $lcUrl = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/generarqrv2";
            $laHeader = ['Accept' => 'application/json'];
            $laBody = [
                "tcCommerceID" => $lcComerceID,
                "tnMoneda" => $lnMoneda,
                "tnTelefono" => $lnTelefono,
                'tcNombreUsuario' => $lcNombreUsuario,
                'tnCiNit' => $lnCiNit,
                'tcNroPago' => $lcNroPago,
                "tnMontoClienteEmpresa" => $lnMontoClienteEmpresa,
                "tcCorreo" => $lcCorreo,
                'tcUrlCallBack' => $lcUrlCallBack,
                "tcUrlReturn" => $lcUrlReturn,
                'taPedidoDetalle' => $laPedidoDetalle
            ];

            $loClient = new Client();
            $loResponse = $loClient->post($lcUrl, [
                'headers' => $laHeader,
                'json' => $laBody
            ]);

            $laResult = json_decode($loResponse->getBody()->getContents());
            $laValues = explode(";", $laResult->values)[1];
            $laQrImage = json_decode($laValues)->qrImage;

            // Respuesta JSON para AJAX
            return response()->json([
                'success' => true,
                'qrImage' => $laQrImage,
                'decodedValues' => $laResult,

            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
