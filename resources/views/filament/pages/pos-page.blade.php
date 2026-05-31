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
    class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:pb-0
           {{ $hasMobileNav ? 'pb-36' : 'pb-20' }}"
>

    {{-- ══ PANEL IZQUIERDO: Catálogo ══ --}}
    <div
        class="lg:col-span-2 space-y-3"
        :class="tab === 'catalog' ? 'block' : 'hidden lg:block'"
    >
        {{-- Sucursal + Barbero: solo visible para cajero y admin --}}
        @if(!$isBarbero)
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
        @endif

        {{-- ── Barra de búsqueda + tabs ─────────────────────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-3 sticky top-0 z-10">
            {{-- Búsqueda --}}
            <input wire:model.live.debounce.300ms="searchTerm" type="search"
                placeholder="Buscar servicio o producto…"
                class="w-full px-3 py-2.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600
                       dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400 mb-2"/>

            {{-- Tabs tipo segmento --}}
            <div class="grid grid-cols-2 gap-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                <button wire:click="$set('activeTab', 'services')"
                    class="flex items-center justify-center gap-1.5 py-2 rounded-md text-sm font-semibold
                           transition-all touch-manipulation
                           {{ $activeTab === 'services'
                               ? 'bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 shadow-sm'
                               : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.175 2.175 0 001.083-1.839l.02-.428m-2.671 2.267l2.077-1.199m0-3.328a4.323 4.323 0 012.068-1.795"/>
                    </svg>
                    Servicios
                </button>
                <button wire:click="$set('activeTab', 'products')"
                    class="flex items-center justify-center gap-1.5 py-2 rounded-md text-sm font-semibold
                           transition-all touch-manipulation
                           {{ $activeTab === 'products'
                               ? 'bg-white dark:bg-gray-800 text-green-600 dark:text-green-400 shadow-sm'
                               : 'text-gray-500 dark:text-gray-400' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                    </svg>
                    Productos
                </button>
            </div>
        </div>

        {{-- ── Catálogo: scroll independiente en móvil ──────── --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow overflow-hidden">
            <div class="overflow-y-auto p-3 lg:p-4
                        max-h-[58dvh] lg:max-h-none
                        overscroll-contain">

                {{-- Grilla de Servicios --}}
                @if($activeTab === 'services')
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @forelse($this->getServices() as $service)
                        <button wire:click="addService({{ $service->id }})"
                            class="flex flex-col justify-between p-3 rounded-xl min-h-[88px]
                                   border-2 border-blue-100 dark:border-blue-900
                                   bg-blue-50 dark:bg-blue-950
                                   hover:border-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900
                                   active:scale-95 transition-all text-left touch-manipulation
                                   focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <p class="font-semibold text-blue-800 dark:text-blue-200 text-sm leading-snug line-clamp-2">
                                {{ $service->name }}
                            </p>
                            <div class="mt-1.5">
                                <p class="text-blue-600 dark:text-blue-300 font-extrabold text-base leading-none">
                                    Bs {{ number_format($service->price, 2) }}
                                </p>
                                <p class="text-xs text-blue-400 mt-0.5">{{ $service->duration_minutes }} min</p>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-2 sm:col-span-3 flex flex-col items-center justify-center py-10 text-gray-400">
                            <svg class="w-10 h-10 mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3z"/>
                            </svg>
                            <p class="text-sm">No hay servicios activos en esta sucursal</p>
                        </div>
                    @endforelse
                </div>
                @endif

                {{-- Grilla de Productos --}}
                @if($activeTab === 'products')
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    @forelse($this->getProducts() as $product)
                        <button wire:click="addProduct({{ $product->id }})"
                            class="flex flex-col justify-between p-3 rounded-xl min-h-[88px]
                                   border-2 border-green-100 dark:border-green-900
                                   bg-green-50 dark:bg-green-950
                                   hover:border-green-400 hover:bg-green-100 dark:hover:bg-green-900
                                   active:scale-95 transition-all text-left touch-manipulation
                                   focus:outline-none focus:ring-2 focus:ring-green-400">
                            <p class="font-semibold text-green-800 dark:text-green-200 text-sm leading-snug line-clamp-2">
                                {{ $product->name }}
                            </p>
                            <div class="mt-1.5">
                                <p class="text-green-600 dark:text-green-300 font-extrabold text-base leading-none">
                                    Bs {{ number_format($product->price, 2) }}
                                </p>
                                <p class="text-xs mt-0.5
                                    {{ $product->isOutOfStock()
                                        ? 'text-red-500 font-bold'
                                        : ($product->isLowStock() ? 'text-orange-400 font-semibold' : 'text-green-400') }}">
                                    Stock: {{ $product->stock }}
                                    @if($product->isLowStock() && !$product->isOutOfStock()) ⚠ @endif
                                </p>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-2 sm:col-span-3 flex flex-col items-center justify-center py-10 text-gray-400">
                            <svg class="w-10 h-10 mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                            </svg>
                            <p class="text-sm">No hay productos con stock en esta sucursal</p>
                        </div>
                    @endforelse
                </div>
                @endif

            </div>{{-- /overflow-y-auto --}}
        </div>
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
                <input wire:model.live="amountPaid" type="number" min="0" step="1"
                    placeholder="{{ number_format($total, 2) }}"
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

            {{-- Comprobante QR (obligatorio cuando pago = QR) --}}
            @if($paymentMethod === 'qr')
            <div>
                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                    Comprobante de pago QR <span class="text-red-400">*</span>
                </label>
                <label class="relative mt-2 flex flex-col items-center justify-center w-full py-5
                              border-2 border-dashed rounded-xl cursor-pointer transition-all
                              {{ $qrReceipt
                                 ? 'border-green-400 bg-green-50 dark:bg-green-950/50'
                                 : 'border-amber-300 hover:border-amber-400 bg-amber-50 dark:bg-amber-950/50' }}">

                    {{-- Input oculto — click sobre el label lo activa --}}
                    <input wire:model="qrReceipt"
                           type="file"
                           accept="image/*"
                           capture="environment"
                           class="absolute inset-0 opacity-0 cursor-pointer w-full h-full"/>

                    @if($qrReceipt)
                        {{-- Estado: archivo adjuntado --}}
                        <svg class="w-9 h-9 text-green-500 mb-1.5" fill="none" viewBox="0 0 24 24"
                             stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <span class="text-sm font-bold text-green-600 dark:text-green-400">Comprobante adjuntado ✓</span>
                        <span class="text-xs text-green-400 mt-0.5">Toca para cambiar la imagen</span>
                    @else
                        {{-- Estado: sin archivo --}}
                        <svg class="w-9 h-9 text-amber-400 mb-1.5" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25
                                   8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574
                                   c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0
                                   1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.776 48.776 0 0
                                   0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
                        </svg>
                        <span class="text-sm font-semibold text-amber-600 dark:text-amber-400">Toca para adjuntar foto del QR</span>
                        <span class="text-xs text-amber-400 mt-0.5">Obligatorio · Abre la cámara en móvil</span>
                    @endif
                </label>

                @error('qrReceipt')
                    <p class="text-red-500 text-xs mt-1 text-center">{{ $message }}</p>
                @enderror
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
    {{-- Tab bar de catálogo/carrito: se eleva sobre el mobile-nav cuando el usuario tiene ese nav --}}
    <div class="fixed inset-x-0 z-40 lg:hidden bg-white dark:bg-gray-900
                border-t border-gray-200 dark:border-gray-700 grid grid-cols-2
                {{ $hasMobileNav ? 'bottom-14' : 'bottom-0' }}"
         style="{{ $hasMobileNav ? 'bottom: calc(3.5rem + env(safe-area-inset-bottom, 0px))' : '' }}">

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
