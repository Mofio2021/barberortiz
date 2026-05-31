<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Caja</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 80mm;
            padding: 4mm;
            color: #000;
            background: #fff;
        }

        .center { text-align: center; }
        .right  { text-align: right; }
        .bold   { font-weight: bold; }
        .small  { font-size: 10px; }
        .large  { font-size: 15px; }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }

        table { width: 100%; border-collapse: collapse; }
        td    { padding: 1.5mm 0; vertical-align: top; }
        td.right { text-align: right; }

        .total-row td {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 2mm;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body { padding: 2mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- Encabezado --}}
    <div class="center bold large">BARBER ORTIZ</div>
    <div class="center small">{{ $cashRegister->branch?->name ?? 'Sucursal' }}</div>
    <div class="center small bold">CIERRE DE CAJA</div>

    <hr class="divider">

    {{-- Info del cierre --}}
    <div>Apertura: {{ $cashRegister->opened_at?->format('d/m/Y H:i') ?? '—' }}</div>
    <div>Cierre:   {{ $cashRegister->closed_at?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</div>
    <div>Cajero:   {{ $cashRegister->user?->name ?? '—' }}</div>

    <hr class="divider">

    {{-- Desglose de ventas --}}
    <div class="bold" style="margin-bottom:1mm;">VENTAS</div>
    <table>
        <tbody>
            <tr>
                <td>Efectivo:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->total_cash_sales, 2) }}</td>
            </tr>
            <tr>
                <td>QR / Billetera:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->total_qr_sales, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total ventas:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->total_cash_sales + (float) $cashRegister->total_qr_sales, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <hr class="divider">

    {{-- Egresos y saldo --}}
    <div class="bold" style="margin-bottom:1mm;">CAJA EFECTIVO</div>
    <table>
        <tbody>
            <tr>
                <td>Saldo inicial:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->opening_balance, 2) }}</td>
            </tr>
            <tr>
                <td>+ Ventas efectivo:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->total_cash_sales, 2) }}</td>
            </tr>
            <tr>
                <td>- Egresos:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->total_expenses, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td>Saldo esperado:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->opening_balance + (float) $cashRegister->total_cash_sales - (float) $cashRegister->total_expenses, 2) }}</td>
            </tr>
            <tr>
                <td>Efectivo contado:</td>
                <td class="right">Bs {{ number_format((float) $cashRegister->closing_balance, 2) }}</td>
            </tr>
            @php
                $diferencia = (float) $cashRegister->closing_balance
                    - ((float) $cashRegister->opening_balance + (float) $cashRegister->total_cash_sales - (float) $cashRegister->total_expenses);
            @endphp
            <tr>
                <td>Diferencia:</td>
                <td class="right" style="{{ $diferencia != 0 ? 'font-weight:bold;' : '' }}">
                    Bs {{ number_format($diferencia, 2) }}
                    {{ $diferencia > 0 ? '(sobrante)' : ($diferencia < 0 ? '(faltante)' : '') }}
                </td>
            </tr>
        </tbody>
    </table>

    @if ($cashRegister->notes)
        <hr class="divider">
        <div class="small">Nota: {{ $cashRegister->notes }}</div>
    @endif

    <hr class="divider">
    <div class="center small">Barber Ortiz — Sistema de Gestión</div>

    {{-- Botón de impresión (no aparece al imprimir) --}}
    <div class="no-print" style="margin-top: 6mm; text-align: center;">
        <button onclick="window.print()"
            style="padding: 6px 20px; font-size: 13px; cursor: pointer;
                   background:#1d1d2b; color:#fff; border:none; border-radius:6px;">
            Imprimir
        </button>
    </div>

    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>

</body>
</html>
