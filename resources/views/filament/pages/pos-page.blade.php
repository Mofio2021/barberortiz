<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════
     MODAL EGRESO RÁPIDO  (capa superior, fuera del grid)
     ═══════════════════════════════════════════════════════ --}}
@if($showExpenseModal)
<div
    class="fixed inset-0 bg-black/60 z-50 flex items-end sm:items-center justify-center p-3"
    wire:click.self="$set('showExpenseModal', false)"
>
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm p-5 space-y-3">

        <div class="flex items-center justify-between">
            <h3 class="font-bold text-base text-gray-900 dark:text-white">Registrar Egreso Rapido</h3>
            <button wire:click="$set('showExpenseModal', false)"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
        </div>

        {{-- Categoría --}}
        <select wire:model="expenseCategory"
            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-red-400 focus:outline-none">
            <option value="insumos">Insumos / Productos</option>
            <option value="servicios">Servicios (agua, luz…)</option>
            <option value="equipo">Equipo / Herramientas</option>
            <option value="marketing">Marketing / Publicidad</option>
            <option value="otros">Otros</option>
        </select>

        {{-- Descripción --}}
        <input wire:model="expenseDescription" type="text" placeholder="Descripcion del egreso…"
            class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-red-400 focus:outline-none"/>

        {{-- Monto --}}
        <input wire:model.live="expenseAmount" type="number" min="0" step="0.5" placeholder="0.00"
            class="w-full px-3 py-3 text-2xl font-bold text-center rounded-lg border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-red-400 focus:outline-none"/>

        {{-- Método de pago --}}
        <div class="grid grid-cols-3 gap-2">
            @foreach(['cash' => 'Efectivo', 'transfer' => 'Transfer.', 'card' => 'Tarjeta'] as $m => $label)
                <button wire:click="$set('expensePaymentMethod', '{{ $m }}')"
                    class="py-2 rounded-lg text-xs font-semibold border-2 transition-all
                    {{ $expensePaymentMethod === $m
                        ? 'bg-red-500 border-red-500 text-white'
                        : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <button wire:click="registerExpense"
            class="w-full py-3 bg-red-500 hover:bg-red-600 active:scale-95 text-white font-bold rounded-xl transition-all touch-manipulation">
            Guardar Egreso — Bs {{ number_format($expenseAmount, 2) }}
        </button>

    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     LAYOUT PRINCIPAL
     Desktop : grid 3 columnas (catálogo 2 + carrito 1)
     Móvil   : una columna con tabs fijos en la parte inferior
     ═══════════════════════════════════════════════════════ --}}
<div
    x-data="{ tab: 'catalog' }"
    class="grid grid-cols-1 lg:grid-cols-3 gap-4 pb-20 lg:pb-0"
>

    {{-- ══ PANEL IZQUIERDO: Catálogo ══ --}}
    <div
        class="lg:col-span-2 space-y-3"
        :class="tab === 'catalog' ? 'block' : 'hidden lg:block'"
    >
        {{-- Sucursal + Barbero --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sucursal</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($this->getBranches() as $branch)
                            <button wire:click="$set('selectedBranchId', {{ $branch->id }})"
                                class="px-4 py-2 rounded-lg text-sm font-medium border-2 transition-all touch-manipulation
                                {{ $selectedBranchId == $branch->id
                                    ? 'bg-amber-500 border-amber-500 text-white'
                                    : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                                {{ $branch->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Barbero</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($this->getStaffList() as $staff)
                            <button wire:click="$set('selectedStaffId', {{ $staff->id }})"
                                class="px-4 py-2 rounded-lg text-sm font-medium border-2 transition-all touch-manipulation
                                {{ $selectedStaffId == $staff->id
                                    ? 'bg-amber-500 border-amber-500 text-white'
                                    : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                                ✂ {{ $staff->name }}
                            </button>
                        @endforeach
                        @if(!$selectedBranchId)
                            <span class="text-xs text-amber-500 self-center">← Selecciona sucursal</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Búsqueda + Tabs servicios/productos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-3">
            <div class="flex gap-2 items-center">
                <input wire:model.live.debounce.300ms="searchTerm" type="search"
                    placeholder="Buscar servicio o producto…"
                    class="flex-1 px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                           dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400"/>
                <button wire:click="$set('activeTab', 'services')"
                    class="px-4 py-2.5 rounded-lg text-sm font-semibold transition-all touch-manipulation whitespace-nowrap
                    {{ $activeTab === 'services'
                        ? 'bg-blue-500 text-white'
                        : 'border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300' }}">
                    ✂ Servicios
                </button>
                <button wire:click="$set('activeTab', 'products')"
                    class="px-4 py-2.5 rounded-lg text-sm font-semibold transition-all touch-manipulation whitespace-nowrap
                    {{ $activeTab === 'products'
                        ? 'bg-green-500 text-white'
                        : 'border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300' }}">
                    Productos
                </button>
            </div>
        </div>

        {{-- Grilla de Servicios --}}
        @if($activeTab === 'services')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($this->getServices() as $service)
                    <button wire:click="addService({{ $service->id }})"
                        class="p-4 rounded-xl border-2 border-blue-100 dark:border-blue-900 bg-blue-50 dark:bg-blue-950
                               hover:border-blue-400 active:scale-95 transition-all text-left touch-manipulation">
                        <div class="font-bold text-blue-800 dark:text-blue-200 text-sm leading-tight">{{ $service->name }}</div>
                        <div class="text-blue-600 dark:text-blue-300 font-extrabold text-lg mt-1">
                            Bs {{ number_format($service->price, 2) }}
                        </div>
                        <div class="text-xs text-blue-400 mt-1">{{ $service->duration_minutes }} min</div>
                    </button>
                @empty
                    <p class="text-gray-400 col-span-3 text-center py-8">No hay servicios activos</p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Grilla de Productos --}}
        @if($activeTab === 'products')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($this->getProducts() as $product)
                    <button wire:click="addProduct({{ $product->id }})"
                        class="p-4 rounded-xl border-2 border-green-100 dark:border-green-900 bg-green-50 dark:bg-green-950
                               hover:border-green-400 active:scale-95 transition-all text-left touch-manipulation">
                        <div class="font-bold text-green-800 dark:text-green-200 text-sm leading-tight">{{ $product->name }}</div>
                        <div class="text-green-600 dark:text-green-300 font-extrabold text-lg mt-1">
                            Bs {{ number_format($product->price, 2) }}
                        </div>
                        <div class="text-xs mt-1 {{ $product->isLowStock() ? 'text-red-400 font-semibold' : 'text-green-400' }}">
                            Stock: {{ $product->stock }}
                        </div>
                    </button>
                @empty
                    <p class="text-gray-400 col-span-3 text-center py-8">No hay productos con stock</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    {{-- ══ PANEL DERECHO: Carrito + Cobro ══ --}}
    <div
        class="space-y-3"
        :class="tab === 'cart' ? 'block' : 'hidden lg:block'"
    >
        {{-- Cliente --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cliente</label>
            <div class="flex gap-2 mt-1">
                <input wire:model.lazy="customerPhone" wire:blur="lookupCustomer" type="tel"
                    placeholder="Telefono (opcional)"
                    class="flex-1 px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                           dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-amber-400 focus:outline-none"/>
                <button wire:click="lookupCustomer"
                    class="px-3 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg touch-manipulation">
                    Buscar
                </button>
            </div>
            @if($customerPhone && $customerName)
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1.5 font-semibold">
                    Cliente: {{ $customerName }}
                </p>
            @endif
        </div>

        {{-- Carrito --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    Carrito ({{ count($cartItems) }})
                </label>
                @if(!empty($cartItems))
                    <button wire:click="resetCart" wire:confirm="Limpiar el carrito?"
                        class="text-xs text-red-400 hover:text-red-600 touch-manipulation">
                        Limpiar todo
                    </button>
                @endif
            </div>

            @if(empty($cartItems))
                <div class="text-center py-8 text-gray-400">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="text-sm">Carrito vacio — agrega servicios o productos</p>
                </div>
            @else
                <div class="space-y-2 max-h-52 overflow-y-auto">
                    @foreach($cartItems as $key => $item)
                        <div class="flex items-center justify-between p-2.5 rounded-lg
                            {{ $item['type'] === 'service'
                                ? 'bg-blue-50 dark:bg-blue-950'
                                : 'bg-green-50 dark:bg-green-950' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">
                                    x{{ $item['quantity'] }} · Bs {{ number_format($item['price'], 2) }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 ml-2 shrink-0">
                                <span class="text-sm font-bold">Bs {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                <button wire:click="removeItem('{{ $key }}')"
                                    class="text-red-400 hover:text-red-600 text-xl leading-none w-6 h-6 flex items-center justify-center touch-manipulation">
                                    &times;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Totales --}}
                <div class="mt-3 pt-3 border-t dark:border-gray-700 space-y-1.5">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                        <span>Subtotal:</span>
                        <span>Bs {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Descuento:</span>
                        <input wire:model.live="discount" type="number" min="0" step="0.5"
                            class="w-24 text-right px-2 py-1 text-sm border rounded-lg dark:bg-gray-700
                                   dark:border-gray-600 dark:text-white focus:ring-1 focus:ring-amber-400"/>
                    </div>
                    <div class="flex justify-between font-bold text-xl text-gray-900 dark:text-white pt-1">
                        <span>TOTAL:</span>
                        <span class="text-amber-500">Bs {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Comision barbero:</span>
                        <span>Bs {{ number_format($totalCommission, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cobro --}}
        @if(!empty($cartItems))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 space-y-3">

            {{-- Métodos de pago --}}
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Metodo de pago</label>
                <div class="grid grid-cols-2 gap-2 mt-2">
                    @foreach(['cash' => 'Efectivo', 'qr' => 'QR / Billetera', 'transfer' => 'Transferencia', 'card' => 'Tarjeta'] as $method => $label)
                        <button wire:click="$set('paymentMethod', '{{ $method }}')"
                            class="py-3 px-2 text-sm rounded-xl border-2 font-semibold transition-all touch-manipulation
                            {{ $paymentMethod === $method
                                ? 'bg-amber-500 border-amber-500 text-white shadow-md'
                                : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Monto recibido (solo efectivo) --}}
            @if($paymentMethod === 'cash')
            <div>
                <label class="text-xs text-gray-500 dark:text-gray-400">Monto recibido (Bs):</label>
                <input wire:model.live="amountPaid" type="number" min="0" step="1" placeholder="0.00"
                    class="w-full mt-1 px-3 py-3 text-center text-2xl font-bold border-2 rounded-xl
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white
                           focus:ring-2 focus:ring-amber-400 focus:outline-none"/>
                @if($amountPaid >= $total && $total > 0)
                    <div class="text-center mt-2 bg-green-100 dark:bg-green-900 rounded-xl py-2.5">
                        <span class="text-green-700 dark:text-green-300 font-bold text-lg">
                            Cambio: Bs {{ number_format($change, 2) }}
                        </span>
                    </div>
                @endif
            </div>
            @endif

            {{-- Notas --}}
            <input wire:model="notes" type="text" placeholder="Notas opcionales…"
                class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600
                       rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none"/>

            {{-- BOTÓN COBRAR --}}
            <button wire:click="processSale"
                wire:confirm="Confirmar venta por Bs {{ number_format($total, 2) }}?"
                class="w-full py-4 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white
                       font-bold text-xl rounded-2xl shadow-lg transition-all touch-manipulation">
                Cobrar Bs {{ number_format($total, 2) }}
            </button>

            {{-- ACCIÓN RÁPIDA: Registrar Egreso --}}
            <button wire:click="$set('showExpenseModal', true)"
                class="w-full py-3 border-2 border-red-300 dark:border-red-700 text-red-600
                       dark:text-red-400 font-semibold text-sm rounded-xl hover:bg-red-50
                       dark:hover:bg-red-950 active:scale-95 transition-all touch-manipulation">
                + Registrar Egreso
            </button>

        </div>
        @endif

    </div>{{-- fin panel derecho --}}

    {{-- ════════════════════════════════════════════════════
         BARRA INFERIOR MÓVIL — oculta en desktop (lg:hidden)
         ════════════════════════════════════════════════════ --}}
    <div class="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white dark:bg-gray-900
                border-t border-gray-200 dark:border-gray-700 grid grid-cols-2 safe-area-inset-bottom">

        <button @click="tab = 'catalog'"
            :class="tab === 'catalog'
                ? 'text-amber-500 border-t-2 border-amber-500'
                : 'text-gray-500 dark:text-gray-400'"
            class="py-3 text-xs font-semibold flex flex-col items-center gap-1 transition-colors touch-manipulation">
            {{-- Scissors icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 0 015.196-3zm1.536-.887a2.175 2.175 0 001.083-1.839l.02-.428m-2.671 2.267l2.077-1.199m0-3.328a4.323 4.323 0 012.068-1.795"/>
            </svg>
            Catalogo
        </button>

        <button @click="tab = 'cart'"
            :class="tab === 'cart'
                ? 'text-amber-500 border-t-2 border-amber-500'
                : 'text-gray-500 dark:text-gray-400'"
            class="py-3 text-xs font-semibold flex flex-col items-center gap-1 transition-colors touch-manipulation relative">
            {{-- Cart icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
            </svg>
            Carrito
            @if(!empty($cartItems))
                <span class="absolute top-2 right-6 bg-amber-500 text-white text-xs font-bold
                             rounded-full w-5 h-5 flex items-center justify-center leading-none">
                    {{ count($cartItems) }}
                </span>
            @endif
        </button>

    </div>

</div>{{-- fin grid principal --}}
</x-filament-panels::page>
