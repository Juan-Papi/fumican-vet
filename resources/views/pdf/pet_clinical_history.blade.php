<!DOCTYPE html>
<html lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Historial Clínico de {{ $pet->name }}</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 11px;
            color: #333;
        }

        @page {
            margin: 40px 50px;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-left,
        .header-right {
            display: table-cell;
            vertical-align: top;
        }

        .header-left {
            width: 60%;
        }

        .header-right {
            text-align: right;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
            color: #007bff;
        }

        .header p {
            margin: 2px 0;
        }

        .pet-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .pet-info table {
            width: 100%;
        }

        .pet-info td {
            padding: 3px;
        }

        .consultation {
            border: 1px solid #eee;
            border-radius: 5px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .consultation-header {
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 5px 5px 0 0;
        }

        .consultation-header h3 {
            margin: 0;
            font-size: 14px;
        }

        .consultation-body {
            padding: 12px;
        }

        .consultation-body h4 {
            font-size: 12px;
            margin-top: 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #eee;
            padding-bottom: 3px;
        }

        .consultation-body p {
            margin: 0 0 5px 0;
        }

        .grid-2-col {
            display: table;
            width: 100%;
        }

        .grid-2-col>div {
            display: table-cell;
            width: 50%;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <h1>Clínica Veterinaria "VetCare"</h1>
            <p>Av. Siempre Viva 742, Santa Cruz, Bolivia</p>
            <p>Tel: (+591) 3-333-3333 | Email: contacto@vetcare.com.bo</p>
        </div>
        <div class="header-right">
            <h2>HISTORIAL CLÍNICO</h2>
            <p>Generado el: {{ now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <div class="pet-info">
        <table>
            <tr>
                <td><strong>Paciente:</strong> {{ $pet->name }}</td>
                <td><strong>Especie:</strong> {{ $pet->breed->specie->name }}</td>
                <td><strong>Raza:</strong> {{ $pet->breed->name }}</td>
            </tr>
            <tr>
                <td><strong>Sexo:</strong> {{ $pet->gender }}</td>
                <td><strong>Fecha de Nac.:</strong> {{ \Carbon\Carbon::parse($pet->birth_date)->format('d/m/Y') }}</td>
                <td><strong>Color:</strong> {{ $pet->color }}</td>
            </tr>
            <tr>
                <!-- CORREGIDO: Se añade el número de teléfono del propietario -->
                <td colspan="3"><strong>Propietario:</strong> {{ $pet->owner->first_name }}
                    {{ $pet->owner->last_name }} (CI: {{ $pet->owner->ci }}) | <strong>Tel:</strong>
                    {{ $pet->owner->phone_number ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @forelse($pet->medicalConsultations as $consultation)
        <div class="consultation">
            <div class="consultation-header">
                <h3>Consulta del {{ $consultation->created_at->format('d/m/Y H:i') }} - Atendido por: Dr(a).
                    {{ $consultation->user->full_name ?? 'N/A' }}</h3>
            </div>
            <div class="consultation-body">
                <h4>Motivo de la Consulta</h4>
                <p>{{ $consultation->reason }}</p>

                <h4>Examen Físico</h4>
                <div class="grid-2-col">
                    <div>
                        <p><strong>Estado General:</strong> {{ $consultation->general_condition }}</p>
                        <p><strong>Apetito:</strong> {{ $consultation->appetite }}</p>
                        <p><strong>Hidratación:</strong> {{ $consultation->hydratation }}</p>
                        <p><strong>Mucosa:</strong> {{ $consultation->mucosa }}</p>
                    </div>
                    <div>
                        <p><strong>Peso:</strong> {{ $consultation->weight }} Kg</p>
                        <p><strong>Temperatura:</strong> {{ $consultation->temperature }} °C</p>
                        <p><strong>Frec. Cardíaca:</strong> {{ $consultation->heart_rate }}</p>
                        <p><strong>Frec. Respiratoria:</strong> {{ $consultation->respiratory_rate }}</p>
                    </div>
                </div>

                <h4>Diagnóstico</h4>
                <p><strong>Presuntivo:</strong> {{ $consultation->presumptive_diagnosis ?? 'N/A' }}</p>
                <p><strong>Confirmativo:</strong> {{ $consultation->confirmatory_diagnosis ?? 'N/A' }}</p>

                <h4>Tratamiento y Evolución</h4>
                <p style="white-space: pre-wrap;">{{ $consultation->treatment ?? 'N/A' }}</p>
            </div>
        </div>
    @empty
        <p style="text-align: center;">Esta mascota no tiene consultas registradas.</p>
    @endforelse

    <div class="footer">
        <p>VetCare - Pasión por la salud de tus mascotas.</p>
        <p>Página
            <script type="text/php">echo $PAGE_NUM . " de " . $PAGE_COUNT;</script>
        </p>
    </div>
</body>

</html>
