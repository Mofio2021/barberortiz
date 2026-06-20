<x-filament-panels::page>

    {{-- ── Filtro de período ── --}}
    <div class="flex flex-wrap items-end gap-3 mb-6">

        {{-- Botones rápidos --}}
        <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 text-sm font-medium">
            @foreach (['hoy' => 'Hoy', 'semana' => 'Semana', 'mes' => 'Mes'] as $key => $label)
                <button
                    wire:click="setPeriodo('{{ $key }}')"
                    class="px-4 py-2 transition-colors
                        {{ $periodo === $key
                            ? 'bg-primary-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Rango personalizado --}}
        <div class="flex items-center gap-2 text-sm">
            <label class="text-gray-600 dark:text-gray-400">Desde</label>
            <input
                type="date"
                wire:model.live="desde"
                wire:change="setPeriodo('personalizado')"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800
                       text-gray-900 dark:text-white px-3 py-2 text-sm shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
            <label class="text-gray-600 dark:text-gray-400">Hasta</label>
            <input
                type="date"
                wire:model.live="hasta"
                wire:change="setPeriodo('personalizado')"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800
                       text-gray-900 dark:text-white px-3 py-2 text-sm shadow-sm
                       focus:outline-none focus:ring-2 focus:ring-primary-500"
            >
        </div>

    </div>

    {{-- ── KPI Cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">

        {{-- Ventas --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ventas</span>
                <span class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-full p-1.5">
                    <x-heroicon-o-banknotes class="w-4 h-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                Bs. {{ number_format($this->resumen['ventas'], 2) }}
            </p>
        </div>

        {{-- Egresos --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Egresos</span>
                <span class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-full p-1.5">
                    <x-heroicon-o-arrow-trending-down class="w-4 h-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                Bs. {{ number_format($this->resumen['egresos'], 2) }}
            </p>
        </div>

        {{-- Comisiones --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Comisiones</span>
                <span class="bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full p-1.5">
                    <x-heroicon-o-user-group class="w-4 h-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                Bs. {{ number_format($this->resumen['comisiones'], 2) }}
            </p>
        </div>

        {{-- Neto --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Neto</span>
                <span class="bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-full p-1.5">
                    <x-heroicon-o-scale class="w-4 h-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                Bs. {{ number_format($this->resumen['neto'], 2) }}
            </p>
        </div>

        {{-- N° Ventas --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ventas (#)</span>
                <span class="bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-full p-1.5">
                    <x-heroicon-o-shopping-cart class="w-4 h-4" />
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">
                {{ $this->resumen['cantidad'] }}
            </p>
        </div>

    </div>

    {{-- ── Fila: Métodos de pago + Comisiones por barbero ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Métodos de pago --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wide">
                Ventas por Método de Pago
            </h3>

            @forelse ($this->ventasPorMetodo as $row)
                <div class="mb-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ $row->label }}</span>
                        <span class="text-gray-500 dark:text-gray-400">
                            Bs. {{ number_format($row->total, 2) }}
                            <span class="ml-1 text-xs">({{ $row->porcentaje }}%)</span>
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                        <div
                            class="h-2 rounded-full bg-primary-500"
                            style="width: {{ $row->porcentaje }}%"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $row->cantidad }} transacciones</p>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Sin ventas en el período.</p>
            @endforelse
        </div>

        {{-- Comisiones por barbero --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wide">
                Comisiones por Barbero
            </h3>

            @forelse ($this->comisionesPorBarbero as $row)
                <div class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center
                                    text-primary-700 dark:text-primary-300 font-bold text-xs">
                            {{ strtoupper(substr($row->staff?->name ?? '?', 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                {{ $row->staff?->name ?? 'Sin asignar' }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $row->servicios }} servicios</p>
                        </div>
                    </div>
                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                        Bs. {{ number_format($row->total_comision, 2) }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Sin comisiones en el período.</p>
            @endforelse
        </div>

    </div>

    {{-- ── Ventas por Barbero (detalle) ── --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm mb-6">

        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                Ventas por Barbero
            </h3>

            <div class="flex flex-wrap items-end gap-2">
                {{-- Filtro barbero --}}
                <select wire:model.live="filterStaffId"
                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                           px-3 py-1.5 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Todos los barberos</option>
                    @foreach ($this->staffList as $staff)
                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                    @endforeach
                </select>

                {{-- Filtro fecha desde --}}
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>Desde</span>
                    <input type="date" wire:model.live="filterVentaDesde"
                        class="rounded-lg border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                               px-2.5 py-1.5 text-sm shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                {{-- Filtro fecha hasta --}}
                <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>Hasta</span>
                    <input type="date" wire:model.live="filterVentaHasta"
                        class="rounded-lg border border-gray-300 dark:border-gray-600
                               bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200
                               px-2.5 py-1.5 text-sm shadow-sm
                               focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
        </div>

        {{-- Cabecera tabla --}}
        <div class="hidden sm:grid grid-cols-4 gap-4 px-3 mb-1 text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
            <span>Fecha</span>
            <span>Barbero</span>
            <span>Tipo de Pago</span>
            <span class="text-right">Monto</span>
        </div>

        @forelse ($this->detalleVentasBarbero as $venta)
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-2.5 px-3
                        border-b border-gray-100 dark:border-gray-700 last:border-0 items-center">

                {{-- Fecha --}}
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-200">
                        {{ $venta->created_at->format('d/m/Y') }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        {{ $venta->created_at->format('H:i') }}
                    </p>
                </div>

                {{-- Barbero --}}
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900/30
                                flex items-center justify-center text-indigo-700 dark:text-indigo-300
                                font-bold text-xs shrink-0">
                        {{ strtoupper(substr($venta->staff?->name ?? '?', 0, 2)) }}
                    </div>
                    <span class="text-sm text-gray-800 dark:text-gray-200 truncate">
                        {{ $venta->staff?->name ?? 'Sin asignar' }}
                    </span>
                </div>

                {{-- Tipo de pago --}}
                <div>
                    @php
                        $metodo = match($venta->payment_method) {
                            'cash'     => ['label' => 'Efectivo',      'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'],
                            'qr'       => ['label' => 'QR',            'class' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300'],
                            'transfer' => ['label' => 'Transferencia', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'],
                            'card'     => ['label' => 'Tarjeta',       'class' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'],
                            default    => ['label' => ucfirst($venta->payment_method), 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $metodo['class'] }}">
                        {{ $metodo['label'] }}
                    </span>
                </div>

                {{-- Monto --}}
                <div class="text-right">
                    <span class="text-sm font-bold text-green-600 dark:text-green-400">
                        Bs. {{ number_format($venta->total, 2) }}
                    </span>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Sin ventas en el período.</p>
        @endforelse
    </div>

    {{-- ── Top Productos ── --}}
    <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 uppercase tracking-wide">
            Top 10 Productos Vendidos
        </h3>

        @forelse ($this->topProductos as $i => $row)
            <div class="flex items-center gap-4 py-2.5 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <span class="w-6 text-center text-xs font-bold text-gray-400 dark:text-gray-500">
                    {{ $i + 1 }}
                </span>
                <p class="flex-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $row->item_name }}
                </p>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $row->total_qty }} uds.
                </span>
                <span class="text-sm font-bold text-gray-800 dark:text-gray-100 text-right w-28">
                    Bs. {{ number_format($row->total_revenue, 2) }}
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Sin productos vendidos en el período.</p>
        @endforelse
    </div>

</x-filament-panels::page>
