<x-filament-widgets::widget>
<div class="p-5 space-y-5">

    {{-- ── CABECERA ─────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                {{ $staff?->name ?? 'Mis estadisticas' }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ now()->isoFormat('dddd D [de] MMMM, YYYY') }}
            </p>
        </div>
        <div class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
            {{-- Reloj de actualización --}}
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25
                       8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Auto · 60s
        </div>
    </div>

    {{-- ── COMISIONES ───────────────────────────────────── --}}
    <div>
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">
            Ganancias por comision
        </p>
        <div class="grid grid-cols-3 gap-3">

            {{-- Hoy --}}
            <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800
                        rounded-xl p-3 text-center">
                <p class="text-xs text-green-600 dark:text-green-400 font-medium mb-1">Hoy</p>
                <p class="text-xl font-extrabold text-green-700 dark:text-green-300 leading-none">
                    Bs {{ number_format((float) $commissionToday, 2) }}
                </p>
                @php $diff = $commissionToday - $commissionYesterday; @endphp
                @if($commissionYesterday > 0)
                    <p class="text-xs mt-1.5 {{ $diff >= 0 ? 'text-green-500' : 'text-red-400' }}">
                        {{ $diff >= 0 ? '+' : '' }}{{ number_format($diff, 2) }} vs ayer
                    </p>
                @endif
            </div>

            {{-- Ayer --}}
            <div class="bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800
                        rounded-xl p-3 text-center">
                <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-1">Ayer</p>
                <p class="text-xl font-extrabold text-blue-700 dark:text-blue-300 leading-none">
                    Bs {{ number_format((float) $commissionYesterday, 2) }}
                </p>
                <p class="text-xs text-blue-400 mt-1.5">
                    {{ now()->subDay()->isoFormat('ddd D') }}
                </p>
            </div>

            {{-- Este mes --}}
            <div class="bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800
                        rounded-xl p-3 text-center">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium mb-1">Este mes</p>
                <p class="text-xl font-extrabold text-amber-700 dark:text-amber-300 leading-none">
                    Bs {{ number_format((float) $commissionMonth, 2) }}
                </p>
                <p class="text-xs text-amber-400 mt-1.5">
                    {{ now()->isoFormat('MMMM') }}
                </p>
            </div>

        </div>
    </div>

    {{-- ── DESCUENTOS Y NETO ───────────────────────────── --}}
    @if($consumptionsMonth > 0 || $pendingPayments->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

        {{-- Consumos del mes --}}
        <div class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 rounded-xl p-3">
            <p class="text-xs text-red-600 dark:text-red-400 font-semibold uppercase tracking-wide mb-1">
                Consumos este mes (descuentos)
            </p>
            <p class="text-lg font-extrabold text-red-700 dark:text-red-300">
                - Bs {{ number_format((float) $consumptionsMonth, 2) }}
            </p>
        </div>

        {{-- Neto a cobrar --}}
        <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-800 rounded-xl p-3">
            <p class="text-xs text-green-600 dark:text-green-400 font-semibold uppercase tracking-wide mb-1">
                Neto a cobrar este mes
            </p>
            <p class="text-lg font-extrabold text-green-700 dark:text-green-300">
                Bs {{ number_format((float) $netMonth, 2) }}
            </p>
        </div>

    </div>
    @endif

    {{-- ── PAGOS PENDIENTES ─────────────────────────────── --}}
    @if($pendingPayments->isNotEmpty())
    <div>
        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-2">
            Pagos pendientes de recibir
        </p>
        <div class="space-y-1.5">
            @foreach($pendingPayments as $pago)
            <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-amber-50 dark:bg-amber-950 border border-amber-200 dark:border-amber-800">
                <span class="text-xs text-amber-700 dark:text-amber-300">
                    {{ $pago->period_start->format('d/m') }} — {{ $pago->period_end->format('d/m/Y') }}
                </span>
                <span class="text-sm font-bold text-amber-800 dark:text-amber-200">
                    Bs {{ number_format((float) $pago->net_amount, 2) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── ÚLTIMO PAGO RECIBIDO ────────────────────────── --}}
    @if($lastPayment)
    <div class="px-3 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 flex items-center justify-between">
        <div>
            <p class="text-xs text-blue-500 dark:text-blue-400 font-semibold">Último pago recibido</p>
            <p class="text-xs text-blue-400">{{ $lastPayment->paid_at->format('d/m/Y') }}</p>
        </div>
        <p class="text-base font-extrabold text-blue-700 dark:text-blue-300">
            Bs {{ number_format((float) $lastPayment->net_amount, 2) }}
        </p>
    </div>
    @endif

    {{-- ── CITAS HOY ────────────────────────────────────── --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                Citas de hoy
                <span class="ml-1 text-gray-400">({{ now()->isoFormat('D [de] MMM') }})</span>
            </p>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full
                {{ $citasHoy->count() > 0 ? 'bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                {{ $citasHoy->count() }} {{ $citasHoy->count() === 1 ? 'cita' : 'citas' }}
            </span>
        </div>

        @if($citasHoy->isEmpty())
            <div class="text-center py-5 text-gray-400 dark:text-gray-500">
                <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25
                           2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0
                           21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                <p class="text-sm">Sin citas para hoy</p>
            </div>
        @else
            <div class="space-y-1.5">
                @foreach($citasHoy as $cita)
                @php
                    $statusColor = match($cita->status) {
                        'confirmada' => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                        'completada' => 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                        'cancelada'  => 'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400',
                        default      => 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300',
                    };
                    $esPasada = $cita->start_at->isPast() && !in_array($cita->status, ['completada','cancelada']);
                @endphp
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl
                    {{ $esPasada ? 'bg-red-50 dark:bg-red-950/40 border border-red-100 dark:border-red-900' : 'bg-gray-50 dark:bg-gray-800/50' }}">

                    {{-- Hora --}}
                    <span class="font-mono text-sm font-bold w-12 shrink-0
                        {{ $esPasada ? 'text-red-500' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $cita->start_at->format('H:i') }}
                    </span>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                            {{ $cita->customer?->name ?? 'Cliente general' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $cita->service?->name ?? '—' }}
                        </p>
                    </div>

                    {{-- Badge de estado --}}
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $statusColor }}">
                        {{ ucfirst($cita->status) }}
                    </span>

                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── CITAS MAÑANA ─────────────────────────────────── --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                Citas de manana
                <span class="ml-1 text-gray-400">({{ now()->addDay()->isoFormat('D [de] MMM') }})</span>
            </p>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full
                {{ $citasManana->count() > 0 ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }}">
                {{ $citasManana->count() }} {{ $citasManana->count() === 1 ? 'cita' : 'citas' }}
            </span>
        </div>

        @if($citasManana->isEmpty())
            <p class="text-sm text-center text-gray-400 dark:text-gray-500 py-3">
                Sin citas agendadas para manana
            </p>
        @else
            <div class="space-y-1.5">
                @foreach($citasManana as $cita)
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50">

                    <span class="font-mono text-sm font-bold w-12 shrink-0 text-gray-600 dark:text-gray-400">
                        {{ $cita->start_at->format('H:i') }}
                    </span>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                            {{ $cita->customer?->name ?? 'Cliente general' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ $cita->service?->name ?? '—' }}
                        </p>
                    </div>

                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full shrink-0
                        bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300">
                        {{ ucfirst($cita->status) }}
                    </span>

                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
</x-filament-widgets::widget>
