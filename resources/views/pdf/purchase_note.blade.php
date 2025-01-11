<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota de Compra</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header, .footer {
            text-align: center;
            margin-bottom: 20px;
        }
        .content {
            margin: 0 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Nota de Compra</h1>
    </div>
    <div class="content">
        <h3>Información General</h3>
        <p><strong>Almacén:</strong> {{ $purchaseNote->warehouse->name }}</p>
        <p><strong>Proveedor:</strong> {{ $purchaseNote->supplier->name }}</p>
        <p><strong>Usuario:</strong> {{ $purchaseNote->user->first_name }} {{ $purchaseNote->user->last_name }}</p>
        <p><strong>Fecha de Compra:</strong> {{ $purchaseNote->purchase_date }}</p>
        <p><strong>Total:</strong> {{ $purchaseNote->total_amount }} Bs</p>

        <h3>Detalles de la Compra</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Medicamento</th>
                    <th>Cantidad</th>
                    <th>Precio de Compra</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($purchaseNoteDetails as $detail)
                    <tr>
                        <td>{{ $detail->medicament->name }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ $detail->purchase_price }} Bs</td>
                        <td>{{ $detail->subtotal }} Bs</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="footer">
        <p>Nota de Compra generada por el sistema.</p>
    </div>
</body>
</html>
