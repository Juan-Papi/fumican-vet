<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .filters {
            margin-bottom: 12px;
        }

        .filters td {
            padding: 4px 8px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #222;
            padding: 6px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        .totals {
            font-weight: bold;
            background: #fafafa;
        }
    </style>
</head>

<body>
    <div class="title">Reporte de Ventas</div>

    {{-- Filtros aplicados --}}
    <table class="filters">
        <tr>
            <td><strong>Cliente:</strong></td>
            <td>
                @if (!empty($filters['customer_id']))
                    {{ optional(\App\Models\Services\Customer::find($filters['customer_id']))->first_name ?? '' }}
                    {{ optional(\App\Models\Services\Customer::find($filters['customer_id']))->last_name ?? '' }}
                @else
                    Todos
                @endif
            </td>
            <td><strong>Almacén:</strong></td>
            <td>
                @if (!empty($filters['warehouse_id']))
                    {{ optional(\App\Models\Sales\Warehouse::find($filters['warehouse_id']))->name ?? '' }}
                @else
                    Todos
                @endif
            </td>
            <td><strong>Desde:</strong></td>
            <td>
                {{ $filters['date_from'] ?? '---' }}
            </td>
            <td><strong>Hasta:</strong></td>
            <td>
                {{ $filters['date_to'] ?? '---' }}
            </td>
        </tr>
    </table>

    {{-- Tabla de ventas --}}
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha de Venta</th>
                <th>Cliente</th>
                <th>Almacén</th>
                <th>Total (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGeneral = 0; @endphp
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->sale_date }}</td>
                    <td>
                        @if ($sale->customer)
                            {{ $sale->customer->first_name }} {{ $sale->customer->last_name }}
                        @else
                            Sin cliente
                        @endif
                    </td>
                    <td>
                        {{ $sale->warehouse->name ?? '---' }}
                    </td>
                    <td style="text-align: right;">
                        {{ number_format($sale->total_amount, 2) }}
                    </td>
                </tr>
                @php $totalGeneral += $sale->total_amount; @endphp
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay registros de ventas con estos filtros.</td>
                </tr>
            @endforelse
            <tr class="totals">
                <td colspan="4" style="text-align: right;">TOTAL GENERAL</td>
                <td style="text-align: right;">{{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tbody>
    </table>
    <br>
    <div style="text-align: right; font-size: 11px;">Generado: {{ now()->format('d/m/Y H:i') }}</div>
</body>

</html>
