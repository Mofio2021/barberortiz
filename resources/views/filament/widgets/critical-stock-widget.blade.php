<x-filament-widgets::widget>
<div class="p-5">

    {{-- ── ENCABEZADO ─────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
            Stock Crítico
        </h2>
        <div class="flex items-center gap-2">
            @if($agotados > 0)
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full
                             bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-300">
                    {{ $agotados }} agotado{{ $agotados > 1 ? 's' : '' }}
                </span>
            @endif
            @if($stockBajo > 0)
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full
                             bg-orange-100 dark:bg-orange-900/50 text-orange-700 dark:text-orange-300">
                    {{ $stockBajo }} bajo mínimo
                </span>
            @endif
            @if($agotados === 0 && $stockBajo === 0)
                <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full
                             bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300">
                    ✓ Todo en stock
                </span>
            @endif
        </div>
    </div>

    {{-- ── TABLA DE PRODUCTOS CRÍTICOS ────────────────────────── --}}
    @if($products->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-500">
            <svg class="w-12 h-12 mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p class="text-sm font-medium">Inventario en buen estado</p>
            <p class="text-xs mt-1">Ningún producto está bajo el mínimo o agotado</p>
        </div>
    @else
        {{-- Encabezado tabla --}}
        <div class="hidden sm:grid grid-cols-[2fr_1fr_auto_auto_auto] gap-x-4 px-3 mb-2
                    text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">
            <span>Producto</span>
            <span>Sucursal</span>
            <span class="text-center">Stock actual</span>
            <span class="text-center">Mínimo</span>
            <span class="text-center">Estado</span>
        </div>

        <div class="space-y-1.5">
            @foreach($products as $product)
            @php
                $isOut  = $product->stock <= 0;
                $isLow  = !$isOut && $product->isLowStock();
                $rowBg  = $isOut  ? 'bg-red-50 dark:bg-red-950/40 border-red-200 dark:border-red-800'
                                  : 'bg-orange-50 dark:bg-orange-950/40 border-orange-200 dark:border-orange-800';
                $badge  = $isOut
                    ? 'bg-red-100 dark:bg-red-900/60 text-red-700 dark:text-red-300'
                    : 'bg-orange-100 dark:bg-orange-900/60 text-orange-700 dark:text-orange-300';
                $label  = $isOut ? 'Agotado' : 'Stock bajo';
            @endphp

            <div class="grid grid-cols-[1fr_auto] sm:grid-cols-[2fr_1fr_auto_auto_auto]
                        items-center gap-x-4 px-3 py-2.5
                        rounded-xl border {{ $rowBg }} transition-colors">

                {{-- Nombre + categoría (siempre visible) --}}
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                        {{ $product->name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $product->category?->name ?? '—' }}
                        @if($product->sku)
                            · <span class="font-mono">{{ $product->sku }}</span>
                        @endif
                    </p>
                </div>

                {{-- Sucursal --}}
                <div class="hidden sm:block">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $product->branch?->name ?? 'Global' }}
                    </span>
                </div>

                {{-- Stock actual --}}
                <div class="hidden sm:flex justify-center">
                    <span class="text-lg font-extrabold {{ $isOut ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400' }}">
                        {{ $product->stock }}
                    </span>
                </div>

                {{-- Mínimo --}}
                <div class="hidden sm:flex justify-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $product->stock_min }}</span>
                </div>

                {{-- Badge de estado (siempre visible) --}}
                <div class="flex justify-end sm:justify-center">
                    <span class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full {{ $badge }}">
                        @if($isOut)
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                            </svg>
                        @else
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        {{ $label }}
                        <span class="sm:hidden ml-1 font-mono opacity-70">({{ $product->stock }}/{{ $product->stock_min }})</span>
                    </span>
                </div>

            </div>
            @endforeach
        </div>
    @endif

</div>
</x-filament-widgets::widget>
