<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Comisiones</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; background: #fff; }

        .header { background: #1d1d2b; color: #fff; padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; font-weight: 700; letter-spacing: 0.5px; }
        .header .periodo { font-size: 10px; color: #9ca3af; margin-top: 3px; }

        .body { padding: 24px; }

        /* KPI grid */
        .kpi-grid { display: flex; gap: 12px; margin-bottom: 24px; }
        .kpi-card { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; }
        .kpi-label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.6px; color: #6b7280; margin-bottom: 4px; }
        .kpi-value { font-size: 16px; font-weight: 700; color: #111827; }
        .kpi-value.green { color: #16a34a; }
        .kpi-value.red   { color: #dc2626; }
        .kpi-value.blue  { color: #2563eb; }
        .kpi-value.purple{ color: #7c3aed; }
        .kpi-value.amber { color: #d97706; }

        /* Sections */
        .section { margin-bottom: 22px; }
        .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px;
                         color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 10px; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 7px 10px; font-size: 9px;
             text-transform: uppercase; letter-spacing: 0.4px; color: #6b7280; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 10px; color: #374151; }
        tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: 700; }
        .text-green { color: #16a34a; }

        /* Progress bar (métodos) */
        .method-row { margin-bottom: 10px; }
        .method-header { display: flex; justify-content: space-between; margin-bottom: 3px; font-size: 10px; }
        .method-bar-bg { background: #e5e7eb; border-radius: 4px; height: 7px; }
        .method-bar { background: #6366f1; border-radius: 4px; height: 7px; }
        .method-sub { font-size: 9px; color: #9ca3af; margin-top: 2px; }

        .two-col { display: flex; gap: 20px; }
        .two-col > div { flex: 1; }

        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1>Reporte General</h1>
            <div class="periodo">Período: {{ $desde }} al {{ $hasta }}</div>
        </div>
        <div style="font-size:10px; color:#9ca3af; text-align:right;">
            Generado el {{ now()->format('d/m/Y H:i') }}<br>
            Barber Ortiz
        </div>
    </div>

    <div class="body">

        {{-- KPI Cards --}}
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-label">Ventas</div>
                <div class="kpi-value green">Bs. {{ number_format($resumen['ventas'], 2) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Egresos</div>
                <div class="kpi-value red">Bs. {{ number_format($resumen['egresos'], 2) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Comisiones</div>
                <div class="kpi-value blue">Bs. {{ number_format($resumen['comisiones'], 2) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Neto</div>
                <div class="kpi-value purple">Bs. {{ number_format($resumen['neto'], 2) }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">N° Ventas</div>
                <div class="kpi-value amber">{{ $resumen['cantidad'] }}</div>
            </div>
        </div>

        {{-- Métodos y Comisiones --}}
        <div class="two-col">

            {{-- Métodos de pago --}}
            <div class="section">
                <div class="section-title">Ventas por Método de Pago</div>
                @forelse ($ventasPorMetodo as $row)
                    <div class="method-row">
                        <div class="method-header">
                            <span>{{ $row->label }}</span>
                            <span class="font-bold">Bs. {{ number_format($row->total, 2) }} ({{ $row->porcentaje }}%)</span>
                        </div>
                        <div class="method-bar-bg">
                            <div class="method-bar" style="width: {{ $row->porcentaje }}%"></div>
                        </div>
                        <div class="method-sub">{{ $row->cantidad }} transacciones</div>
                    </div>
                @empty
                    <p style="color:#9ca3af; font-size:10px;">Sin datos.</p>
                @endforelse
            </div>

            {{-- Comisiones --}}
            <div class="section">
                <div class="section-title">Comisiones por Barbero</div>
                <table>
                    <thead>
                        <tr>
                            <th>Barbero</th>
                            <th class="text-center">Servicios</th>
                            <th class="text-right">Comisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comisionesBarbero as $row)
                            <tr>
                                <td>{{ $row->staff?->name ?? 'Sin asignar' }}</td>
                                <td class="text-center">{{ $row->servicios }}</td>
                                <td class="text-right text-green font-bold">Bs. {{ number_format($row->total_comision, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center" style="color:#9ca3af;">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        {{-- Ventas por Barbero --}}
        <div class="section">
            <div class="section-title">Ventas por Barbero</div>
            <table>
                <thead>
                    <tr>
                        <th>Barbero</th>
                        <th class="text-center">Ventas</th>
                        <th class="text-center">Items</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Comisión</th>
                        <th class="text-right">Neto</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ventasBarbero as $row)
                        <tr>
                            <td>{{ $row->staff?->name ?? 'Sin asignar' }}</td>
                            <td class="text-center">{{ $row->ventas }}</td>
                            <td class="text-center">{{ $row->items }}</td>
                            <td class="text-right text-green font-bold">Bs. {{ number_format($row->total_vendido, 2) }}</td>
                            <td class="text-right" style="color:#2563eb;">Bs. {{ number_format($row->total_comision, 2) }}</td>
                            <td class="text-right" style="color:#7c3aed; font-weight:700;">Bs. {{ number_format($row->neto, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center" style="color:#9ca3af;">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Top Productos --}}
        <div class="section">
            <div class="section-title">Top 10 Productos Vendidos</div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th class="text-center">Unidades</th>
                        <th class="text-right">Ingresos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topProductos as $i => $row)
                        <tr>
                            <td class="text-center" style="color:#9ca3af;">{{ $i + 1 }}</td>
                            <td>{{ $row->item_name }}</td>
                            <td class="text-center">{{ $row->total_qty }}</td>
                            <td class="text-right font-bold">Bs. {{ number_format($row->total_revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center" style="color:#9ca3af;">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <div class="footer">
        Reporte generado automáticamente por el sistema Barber Ortiz
    </div>

</body>
</html>
