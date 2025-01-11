<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Venta</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
            padding: 10px 0;
        }
        .header {
            border-bottom: 1px solid #ddd;
        }
        .footer {
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        .content {
            margin-top: 20px;
        }
        .content h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .info, .details {
            width: 100%;
            margin-bottom: 20px;
        }
        .info th, .info td, .details th, .details td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .info th, .details th {
            background-color: #f2f2f2;
        }
        .details {
            border-collapse: collapse;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nota de Venta</h1>
        </div>
        <div class="content">
            <h2>Información General</h2>
            <table class="info">
                <tr>
                    <th>Almacén</th>
                    <td>{{ $salesNote->warehouse->name }}</td>
                </tr>
                <tr>
                    <th>Cliente</th>
                    <td>{{ $salesNote->customer->first_name }} {{ $salesNote->customer->last_name }}</td>
                </tr>
                <tr>
                    <th>Usuario</th>
                    <td>{{ $salesNote->user->first_name }} {{ $salesNote->user->last_name }}</td>
                </tr>
                <tr>
                    <th>Fecha de Venta</th>
                    <td>{{ $salesNote->sale_date }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td>{{ $salesNote->total_amount }} Bs</td>
                </tr>
            </table>

            <h2>Detalles de la Venta</h2>
            <table class="details">
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Cantidad</th>
                        <th>Precio de Venta</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesNoteDetails as $detail)
                        <tr>
                            <td>{{ $detail->medicament->name }}</td>
                            <td>{{ $detail->quantity }}</td>
                            <td>{{ $detail->sale_price }} Bs</td>
                            <td>{{ $detail->subtotal }} Bs</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="footer">
            <p>Nota de Venta generada el {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>
</body>
</html>
