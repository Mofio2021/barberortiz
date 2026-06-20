<x-filament-widgets::widget>
<div class="p-5 space-y-5">

    {{-- ── ENCABEZADO ─────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

        {{-- Título --}}
        <div>
            <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
                Resumen Financiero
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $label }} · {{ $selectedBranchName }}
            </p>
        </div>

        {{-- Filtros: período + sucursal --}}
        <div class="flex flex-wrap items-center gap-2">

            {{-- Selector de período --}}
            <div class="inline-flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1 gap-1">
                @foreach(['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes'] as $key => $lbl)
                    <button wire:click="setPeriod('{{ $key }}')"
                        class="px-3 py-1.5 text-xs font-semibold rounded-md transition-all touch-manipulation
                               {{ $period === $key
                                   ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow-sm'
                                   : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>

            {{-- Selector de sucursal (solo super_admin) --}}
            @if($isSuperAdmin && $branches->isNotEmpty())
            <select wire:model.live="selectedBranchId"
                class="text-xs border border-gray-300 dark:border-gray-600 rounded-lg
                       px-2.5 py-1.5 bg-white dark:bg-gray-800
                       text-gray-700 dark:text-gray-200
                       focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">Todas las sucursales</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @endif
        </div>
    </div>

    {{-- ── KPIs PRINCIPALES (4 tarjetas) ─────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Ventas --}}
        <div class="bg-green-50 dark:bg-green-950/60 border border-green-200 dark:border-green-800 rounded-xl p-4">
            <div class="flex items-start justify-between mb-2">
                <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wide">Ventas</p>
                @if($tendencia === 'up')
                    <svg class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                    </svg>
                @else
                    <svg class="w-4 h-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6 9 12.75l4.286-4.286a11.948 11.948 0 0 1 4.306 6.43l.776 2.898m0 0 3.182-5.511m-3.182 5.51-5.511-3.181"/>
                    </svg>
                @endif
            </div>
            <p class="text-xl font-extrabold text-green-700 dark:text-green-300 leading-none">
                Bs {{ number_format($ventas, 2) }}
            </p>
            <p class="text-xs text-green-500 dark:text-green-400 mt-1.5">
                {{ $cantVentas }} transacciones ·
                @if($diffVentas >= 0)
                    <span class="text-green-600 dark:text-green-400">+Bs {{ number_format(abs($diffVentas), 2) }}</span>
                @else
                    <span class="text-red-500">-Bs {{ number_format(abs($diffVentas), 2) }}</span>
                @endif
                vs anterior
            </p>
        </div>

        {{-- Egresos --}}
        <div class="bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide mb-2">Egresos</p>
            <p class="text-xl font-extrabold text-red-700 dark:text-red-300 leading-none">
                Bs {{ number_format($egresos, 2) }}
            </p>
            <p class="text-xs text-red-400 mt-1.5">Gastos operativos registrados</p>
        </div>

        {{-- Costo de Productos --}}
        <div class="bg-orange-50 dark:bg-orange-950/60 border border-orange-200 dark:border-orange-800 rounded-xl p-4">
            <p class="text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase tracking-wide mb-2">Costo Productos</p>
            <p class="text-xl font-extrabold text-orange-700 dark:text-orange-300 leading-none">
                Bs {{ number_format($costoProductos, 2) }}
            </p>
            <p class="text-xs text-orange-400 mt-1.5">Precio de costo × unidades vendidas</p>
        </div>

        {{-- Ganancia Neta --}}
        @if($ganancia >= 0)
        <div class="bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-2">Ganancia Neta</p>
            <p class="text-xl font-extrabold text-blue-700 dark:text-blue-300 leading-none">
                Bs {{ number_format($ganancia, 2) }}
            </p>
            <p class="text-xs text-blue-400 mt-1.5">
                Margen: {{ $margenPct }}%
                <span class="ml-1 opacity-70">(ventas − comisiones − egresos − costo)</span>
            </p>
        </div>
        @else
        <div class="bg-red-50 dark:bg-red-950/60 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide mb-2">Ganancia Neta</p>
            <p class="text-xl font-extrabold text-red-700 dark:text-red-300 leading-none">
                Bs {{ number_format($ganancia, 2) }}
            </p>
            <p class="text-xs text-red-400 mt-1.5">
                Margen: {{ $margenPct }}%
                <span class="ml-1 opacity-70">(ventas − comisiones − egresos − costo)</span>
            </p>
        </div>
        @endif
    </div>

    {{-- ── MÉTRICAS SECUNDARIAS ─────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">

        {{-- Ticket promedio --}}
        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3">
            <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a3 3 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Ticket Promedio</p>
                <p class="font-bold text-gray-800 dark:text-white">Bs {{ number_format($ticketPromedio, 2) }}</p>
            </div>
        </div>

        {{-- Comisiones --}}
        <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3">
            <div class="w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Comisiones Staff</p>
                <p class="font-bold text-gray-800 dark:text-white">Bs {{ number_format($comisiones, 2) }}</p>
            </div>
        </div>

        {{-- Fórmula legible actualizada --}}
        <div class="col-span-2 lg:col-span-1 flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 rounded-xl px-4 py-3">
            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono leading-relaxed space-y-0.5">
                <div>
                    <span class="text-green-600 dark:text-green-400">{{ number_format($ventas, 2) }}</span>
                    <span class="mx-1 text-gray-400">−</span>
                    <span class="text-amber-500">{{ number_format($comisiones, 2) }}</span>
                    <span class="mx-1 text-gray-400">−</span>
                    <span class="text-red-500">{{ number_format($egresos, 2) }}</span>
                    <span class="mx-1 text-gray-400">−</span>
                    <span class="text-orange-500">{{ number_format($costoProductos, 2) }}</span>
                </div>
                <div>
                    <span class="text-gray-400">= </span>
                    <span class="{{ $ganancia >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600' }} font-bold">
                        Bs {{ number_format($ganancia, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── REPORTE POR BARBERO ───────────────────────────── --}}
    @if($staffStats->isNotEmpty())
    <div>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
            </svg>
            Comisiones por Barbero · {{ $label }}
        </h3>

        {{-- Encabezados (escritorio) --}}
        <div class="hidden sm:grid grid-cols-[2fr_1fr_1fr_1fr] gap-x-4 px-3 mb-1.5
                    text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">
            <span>Barbero</span>
            <span class="text-right">Ventas</span>
            <span class="text-right">Facturado</span>
            <span class="text-right">Comisión</span>
        </div>

        <div class="space-y-1.5">
            @foreach($staffStats as $stat)
            @php
                $pct = $ventas > 0 ? round(($stat->total_facturado / $ventas) * 100) : 0;
            @endphp
            <div class="grid grid-cols-[1fr_auto] sm:grid-cols-[2fr_1fr_1fr_1fr]
                        items-center gap-x-4 px-3 py-2.5
                        bg-gray-50 dark:bg-gray-800/50 rounded-xl">

                {{-- Nombre + barra de participación --}}
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                        {{ $stat->name }}
                    </p>
                    <div class="mt-1 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden w-full">
                        <div class="h-full bg-amber-400 rounded-full"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5 sm:hidden">
                        {{ $stat->total_ventas }} ventas ·
                        Bs {{ number_format($stat->total_facturado, 2) }} facturado
                    </p>
                </div>

                {{-- Ventas (desktop) --}}
                <div class="hidden sm:block text-right">
                    <span class="text-sm text-gray-600 dark:text-gray-300">
                        {{ $stat->total_ventas }}
                    </span>
                    <p class="text-xs text-gray-400">transacciones</p>
                </div>

                {{-- Facturado (desktop) --}}
                <div class="hidden sm:block text-right">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Bs {{ number_format($stat->total_facturado, 2) }}
                    </span>
                    <p class="text-xs text-gray-400">{{ $pct }}% del total</p>
                </div>

                {{-- Comisión (siempre visible) --}}
                <div class="text-right">
                    <span class="text-sm font-bold text-amber-600 dark:text-amber-400">
                        Bs {{ number_format($stat->total_comision, 2) }}
                    </span>
                    <p class="text-xs text-gray-400">comisión</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Total de comisiones --}}
        <div class="mt-2 flex items-center justify-between px-3 py-2
                    bg-amber-50 dark:bg-amber-950/40 rounded-xl
                    border border-amber-200 dark:border-amber-800">
            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                Total comisiones a pagar
            </span>
            <span class="text-sm font-extrabold text-amber-700 dark:text-amber-300">
                Bs {{ number_format($comisiones, 2) }}
            </span>
        </div>
    </div>
    @endif

</div>
</x-filament-widgets::widget>
