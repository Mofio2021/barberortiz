<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #{{ $sale->id }}</title>
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

        .center  { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }
        .small   { font-size: 10px; }
        .large   { font-size: 15px; }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        td { padding: 1mm 0; vertical-align: top; }
        td.qty   { width: 8mm; }
        td.price { width: 20mm; text-align: right; }
        td.name  { }

        .total-row td {
            font-weight: bold;
            font-size: 13px;
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
    <div class="center small">{{ $sale->branch?->name ?? 'Sucursal' }}</div>
    <div class="center small">{{ now()->format('d/m/Y H:i') }}</div>

    <hr class="divider">

    {{-- Info de la venta --}}
    <div>Ticket #{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</div>
    <div>Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}</div>
    <div>Cliente: {{ $sale->customer?->name ?? 'Consumidor final' }}</div>
    @if ($sale->staff)
        <div>Atendido: {{ $sale->staff->name }}</div>
    @endif

    <hr class="divider">

    {{-- Ítems --}}
    <table>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td class="qty">{{ (int) $item->quantity }}x</td>
                    <td class="name">{{ $item->item_name }}</td>
                    <td class="price">Bs {{ number_format((float) $item->price_at_time * $item->quantity, 2) }}</td>
                </tr>
                @if ($item->quantity > 1)
                    <tr>
                        <td class="qty"></td>
                        <td class="name small">  @ Bs {{ number_format((float) $item->price_at_time, 2) }} c/u</td>
                        <td class="price"></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <hr class="divider">

    {{-- Totales --}}
    <table>
        <tbody>
            @if ((float) $sale->discount > 0)
                <tr>
                    <td>Subtotal:</td>
                    <td class="right">Bs {{ number_format((float) $sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Descuento:</td>
                    <td class="right">- Bs {{ number_format((float) $sale->discount, 2) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td>TOTAL:</td>
                <td class="right">Bs {{ number_format((float) $sale->total, 2) }}</td>
            </tr>
            @if ($sale->payment_method === 'cash')
                <tr>
                    <td>Recibido:</td>
                    <td class="right">Bs {{ number_format((float) $sale->amount_paid, 2) }}</td>
                </tr>
                <tr>
                    <td>Cambio:</td>
                    <td class="right">Bs {{ number_format((float) $sale->change_given, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <hr class="divider">

    {{-- Método de pago --}}
    <div class="center">Pago: <span class="bold">{{ $sale->payment_label }}</span></div>

    <hr class="divider">

    <div class="center small">¡Gracias por su visita!</div>
    <div class="center small">Vuelva pronto.</div>

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
