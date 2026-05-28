<x-filament-panels::page>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">

    {{-- ═══ PANEL IZQUIERDO: Catálogo ═══ --}}
    <div class="lg:col-span-2 space-y-3">

        {{-- Sucursal + Barbero --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sucursal</label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($this->getBranches() as $branch)
                            <button wire:click="$set('selectedBranchId', {{ $branch->id }})"
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border-2 transition-all
                                {{ $selectedBranchId == $branch->id ? 'bg-amber-500 border-amber-500 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
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
                                class="px-3 py-1.5 rounded-lg text-sm font-medium border-2 transition-all
                                {{ $selectedStaffId == $staff->id ? 'bg-amber-500 border-amber-500 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                                ✂ {{ $staff->name }}
                            </button>
                        @endforeach
                        @if(!$selectedBranchId)
                            <span class="text-xs text-amber-500">← Selecciona sucursal</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Búsqueda + Tabs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-3">
            <div class="flex gap-3 items-center">
                <input wire:model.live.debounce.300ms="searchTerm" type="text"
                    placeholder="🔍 Buscar servicio o producto..."
                    class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-400"/>
                <button wire:click="$set('activeTab', 'services')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'services' ? 'bg-blue-500 text-white' : 'border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300' }}">
                    ✂ Servicios
                </button>
                <button wire:click="$set('activeTab', 'products')"
                    class="px-4 py-2 rounded-lg text-sm font-semibold transition-all {{ $activeTab === 'products' ? 'bg-green-500 text-white' : 'border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300' }}">
                    📦 Productos
                </button>
            </div>
        </div>

        {{-- Servicios --}}
        @if($activeTab === 'services')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($this->getServices() as $service)
                    <button wire:click="addService({{ $service->id }})"
                        class="p-3 rounded-xl border-2 border-blue-100 dark:border-blue-900 bg-blue-50 dark:bg-blue-950 hover:border-blue-400 transition-all text-left">
                        <div class="font-semibold text-blue-800 dark:text-blue-200 text-sm">{{ $service->name }}</div>
                        <div class="text-blue-600 dark:text-blue-300 font-bold mt-1">Bs {{ number_format($service->price, 2) }}</div>
                        <div class="text-xs text-blue-400 mt-0.5">{{ $service->duration_minutes }} min</div>
                    </button>
                @empty
                    <p class="text-gray-400 col-span-3 text-center py-6">No hay servicios activos</p>
                @endforelse
            </div>
        </div>
        @endif

        {{-- Productos --}}
        @if($activeTab === 'products')
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @forelse($this->getProducts() as $product)
                    <button wire:click="addProduct({{ $product->id }})"
                        class="p-3 rounded-xl border-2 border-green-100 dark:border-green-900 bg-green-50 dark:bg-green-950 hover:border-green-400 transition-all text-left">
                        <div class="font-semibold text-green-800 dark:text-green-200 text-sm">{{ $product->name }}</div>
                        <div class="text-green-600 dark:text-green-300 font-bold mt-1">Bs {{ number_format($product->price, 2) }}</div>
                        <div class="text-xs {{ $product->isLowStock() ? 'text-red-400 font-semibold' : 'text-green-400' }} mt-0.5">
                            Stock: {{ $product->stock }}
                        </div>
                    </button>
                @empty
                    <p class="text-gray-400 col-span-3 text-center py-6">No hay productos con stock</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    {{-- ═══ PANEL DERECHO: Carrito ═══ --}}
    <div class="space-y-3">

        {{-- Cliente --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cliente</label>
            <div class="flex gap-2 mt-1">
                <input wire:model.lazy="customerPhone" wire:blur="lookupCustomer" type="tel"
                    placeholder="Teléfono (opcional)"
                    class="flex-1 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-amber-400 focus:outline-none"/>
                <button wire:click="lookupCustomer" class="px-3 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600">🔍</button>
            </div>
            @if($customerPhone && $customerName)
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-semibold">👤 {{ $customerName }}</p>
            @endif
        </div>

        {{-- Carrito --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Carrito ({{ count($cartItems) }})</label>
                @if(!empty($cartItems))
                    <button wire:click="resetCart" class="text-xs text-red-400 hover:text-red-600">🗑 Limpiar</button>
                @endif
            </div>

            @if(empty($cartItems))
                <div class="text-center py-8 text-gray-400">
                    <div class="text-4xl mb-2">🛒</div>
                    <p class="text-sm">Carrito vacío</p>
                </div>
            @else
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($cartItems as $key => $item)
                        <div class="flex items-center justify-between p-2 rounded-lg {{ $item['type'] === 'service' ? 'bg-blue-50 dark:bg-blue-950' : 'bg-green-50 dark:bg-green-950' }}">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">x{{ $item['quantity'] }} · Bs {{ number_format($item['price'], 2) }}</p>
                            </div>
                            <div class="flex items-center gap-2 ml-2">
                                <span class="text-sm font-bold">Bs {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                <button wire:click="removeItem('{{ $key }}')" class="text-red-400 hover:text-red-600 text-lg leading-none">×</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Totales --}}
                <div class="mt-3 pt-3 border-t dark:border-gray-700 space-y-1.5">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                        <span>Subtotal:</span><span>Bs {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">Descuento:</span>
                        <input wire:model.live="discount" type="number" min="0" step="0.5"
                            class="w-20 text-right px-2 py-1 text-sm border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white"/>
                    </div>
                    <div class="flex justify-between font-bold text-lg text-gray-900 dark:text-white">
                        <span>TOTAL:</span>
                        <span class="text-amber-500">Bs {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Comisión barbero:</span><span>Bs {{ number_format($totalCommission, 2) }}</span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Cobro --}}
        @if(!empty($cartItems))
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
            <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Método de pago</label>
            <div class="grid grid-cols-2 gap-2 mt-2">
                @foreach(['cash'=>'💵 Efectivo','qr'=>'📱 QR','transfer'=>'🏦 Transfer','card'=>'💳 Tarjeta'] as $method=>$label)
                    <button wire:click="$set('paymentMethod', '{{ $method }}')"
                        class="py-2 px-2 text-xs rounded-lg border-2 font-medium transition-all
                        {{ $paymentMethod === $method ? 'bg-amber-500 border-amber-500 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if($paymentMethod === 'cash')
            <div class="mt-3">
                <label class="text-xs text-gray-500">Monto recibido (Bs):</label>
                <input wire:model.live="amountPaid" type="number" min="0" step="1" placeholder="0.00"
                    class="w-full mt-1 px-3 py-2 text-center text-xl font-bold border-2 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-amber-400 focus:outline-none"/>
                @if($amountPaid >= $total && $total > 0)
                    <div class="text-center mt-2 bg-green-100 dark:bg-green-900 rounded-lg py-2">
                        <span class="text-green-700 dark:text-green-300 font-bold">Cambio: Bs {{ number_format($change, 2) }}</span>
                    </div>
                @endif
            </div>
            @endif

            <input wire:model="notes" type="text" placeholder="Notas..."
                class="w-full mt-3 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"/>

            <button wire:click="processSale"
                wire:confirm="¿Confirmar venta por Bs {{ number_format($total, 2) }}?"
                class="mt-3 w-full py-3 bg-amber-500 hover:bg-amber-600 text-white font-bold text-lg rounded-xl shadow transition-all active:scale-95">
                ✓ Cobrar Bs {{ number_format($total, 2) }}
            </button>
        </div>
        @endif
    </div>
</div>
</x-filament-panels::page>
