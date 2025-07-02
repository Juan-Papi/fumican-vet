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

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'petName' => 'required|string|max:255',
            'service' => 'required|string|max:255',
            'date' => 'required|date',
            'timeSlot' => 'required|string|max:50',
            'comment' => 'nullable|string|max:500',
        ]);

        // $data = [
        //     'name' => 'Juan Pérez',
        //     'phone' => '555-1234567',
        //     'email' => 'juan@example.com',
        //     'petName' => 'Firulais',
        //     'service' => 'Consulta',
        //     'date' => '2025-07-05',
        //     'timeSlot' => '10:00',
        //     'comment' => 'Ninguno',
        // ];
        $data = [
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'petName' => $request->input('petName'),
            'service' => $request->input('service'),
            'date' => $request->input('date'),
            'timeSlot' => $request->input('timeSlot'),
            'comment' => $request->input('comment', 'Ninguno'),
        ];

        Log::info('generando pdf', [
            'request' => $data
        ]);
        // $data = $request->all();

        $pdf = PDF::loadView('pdf.reserve', compact('data'));
        return $pdf->download('reserva.pdf');
    }

    public function qr(Request $request)
    {
        try {
            Log::info('request para generar QR', [
                'request' => $request->all()
            ]);
            // Parámetros necesarios para generar el QR
            $lcComerceID = "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c";
            $lnMoneda = 2;
            $lnTelefono = $request->phone;
            $lcNombreUsuario = $request->name;
            $lnCiNit = $request->phone;
            $lcNroPago = "test-grupo17sa" . rand(100000, 999999);
            $lnMontoClienteEmpresa = 0.01;
            $lcCorreo = $request->email;
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

            $laRawValues = explode(";", $laResult->values);
            $lcNumeroTransaccion = $laRawValues[0] ?? null;
            Log::info('transsaccion: ' . $lcNumeroTransaccion);
            // Respuesta JSON para AJAX
            return response()->json([
                'success' => true,
                'qrImage' => $laQrImage,
                'numeroTransaccion' => $lcNumeroTransaccion,

            ]);
        } catch (\Throwable $th) {
            Log::error('Error al generar el QR', [
                'exception' => $th,
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    function checkPayment(string $transaccionId)
    {
        try {
            $client = new Client();

            $response = $client->post("https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'TransaccionDePago' => $transaccionId
                ])
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Throwable $th) {
            Log::error('Error al consultar pago TigoMoney', [
                'exception' => $th,
                'transaccionId' => $transaccionId
            ]);

            return [
                'success' => false,
                'message' => $th->getMessage()
            ];
        }
    }

    public function verificarPago(Request $request)
    {
        $numeroTransaccion = $request->numeroTransaccion;

        try {
            Log::info('verificando pago', [
                'numeroTransaccion' => $numeroTransaccion
            ]);
            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion', [
                'headers' => ['Accept' => 'application/json'],
                'json' => ['TransaccionDePago' => $numeroTransaccion]
            ]);

            $data = json_decode($response->getBody()->getContents());

            return response()->json([
                'data' => $data->values,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'pagado' => false,
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
