<x-filament-panels::page>

{{-- ═══════════════════════════════════════════════════════
     MODAL EGRESO RÁPIDO  (capa superior, fuera del grid)
     ═══════════════════════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════
     MODAL: ABRIR CAJA
     ═══════════════════════════════════════════════════════ --}}
@if($showOpenRegisterModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:60;display:flex;align-items:flex-end;justify-content:center;padding:12px"
     wire:click.self="$set('showOpenRegisterModal', false)">
    <div style="background:#1f2937;border-radius:1rem;width:100%;max-width:22rem;padding:1.25rem;display:flex;flex-direction:column;gap:.875rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-weight:700;font-size:.95rem;color:#fff;">🟢 Abrir Caja</h3>
            <button wire:click="$set('showOpenRegisterModal', false)"
                style="color:#9ca3af;font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>
        <div>
            <label style="font-size:.75rem;color:#9ca3af;display:block;margin-bottom:.375rem;">Saldo Inicial en Efectivo (Bs)</label>
            <input wire:model.live="openingBalance" type="number" min="0" step="1" placeholder="0.00"
                style="width:100%;padding:.875rem;font-size:1.75rem;font-weight:700;text-align:center;
                       border-radius:.75rem;border:2px solid #374151;background:#111827;color:#fff;
                       focus:outline:none;box-sizing:border-box;"/>
            <p style="font-size:.7rem;color:#6b7280;margin-top:.25rem;">Dinero en caja al momento de abrir el turno.</p>
        </div>
        <button wire:click="openRegister"
            style="width:100%;padding:.875rem;background:#10b981;color:#fff;font-weight:700;border-radius:.875rem;
                   border:none;cursor:pointer;font-size:1rem;touch-action:manipulation;">
            Abrir Caja — Bs {{ number_format($openingBalance, 2) }}
        </button>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     MODAL: CERRAR CAJA
     ═══════════════════════════════════════════════════════ --}}
@if($showCloseRegisterModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:60;display:flex;align-items:flex-end;justify-content:center;padding:12px"
     wire:click.self="$set('showCloseRegisterModal', false)">
    <div style="background:#1f2937;border-radius:1rem;width:100%;max-width:22rem;padding:1.25rem;display:flex;flex-direction:column;gap:.875rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-weight:700;font-size:.95rem;color:#fff;">🔴 Cerrar Caja</h3>
            <button wire:click="$set('showCloseRegisterModal', false)"
                style="color:#9ca3af;font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>
        <div>
            <label style="font-size:.75rem;color:#9ca3af;display:block;margin-bottom:.375rem;">Efectivo contado físicamente (Bs)</label>
            <input wire:model.live="countedCash" type="number" min="0" step="1" placeholder="0.00"
                style="width:100%;padding:.875rem;font-size:1.75rem;font-weight:700;text-align:center;
                       border-radius:.75rem;border:2px solid #374151;background:#111827;color:#fff;box-sizing:border-box;"/>
        </div>
        <div>
            <label style="font-size:.75rem;color:#9ca3af;display:block;margin-bottom:.375rem;">Observaciones (opcional)</label>
            <input wire:model="closeNotes" type="text" placeholder="Ej: Sin novedades…"
                style="width:100%;padding:.625rem .75rem;font-size:.875rem;border-radius:.625rem;
                       border:1px solid #374151;background:#111827;color:#fff;box-sizing:border-box;"/>
        </div>
        <p style="font-size:.7rem;color:#6b7280;">Al confirmar se calcularán automáticamente los totales de efectivo, QR y egresos del turno.</p>
        <button wire:click="closeRegister"
            wire:confirm="¿Confirmar cierre de caja?"
            style="width:100%;padding:.875rem;background:#ef4444;color:#fff;font-weight:700;border-radius:.875rem;
                   border:none;cursor:pointer;font-size:1rem;touch-action:manipulation;">
            Confirmar Cierre de Caja
        </button>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     MODAL: EGRESO RÁPIDO
     z-index:100, overflow-y:auto, max-height:90vh
     Botón NARANJA para distinguirlo del botón COBRAR (ámbar)
     ═══════════════════════════════════════════════════════ --}}
@if($showExpenseModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:100;
            display:flex;align-items:flex-end;justify-content:center;padding:0 12px 12px;"
     wire:click.self="$set('showExpenseModal', false)">

    <div style="background:#1f2937;border-radius:1.125rem;width:100%;max-width:24rem;
                overflow-y:auto;max-height:90vh;
                padding-bottom:env(safe-area-inset-bottom, 12px);
                display:flex;flex-direction:column;gap:.875rem;padding:1.375rem;">

        {{-- Cabecera --}}
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-weight:700;font-size:1rem;color:#fff;display:flex;align-items:center;gap:.5rem;">
                <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#f97316;"></span>
                Registrar Egreso
            </h3>
            <button wire:click="$set('showExpenseModal', false)"
                style="color:#9ca3af;font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;padding:0;">&times;</button>
        </div>

        {{-- Categoría --}}
        <div>
            <label style="font-size:.72rem;color:#9ca3af;display:block;margin-bottom:.3rem;">Categoría</label>
            <select wire:model="expenseCategory"
                style="width:100%;padding:.625rem .75rem;font-size:.875rem;border-radius:.625rem;
                       border:1px solid #374151;background:#111827;color:#fff;box-sizing:border-box;appearance:none;">
                <option value="insumos">Insumos / Productos</option>
                <option value="servicios">Servicios (agua, luz…)</option>
                <option value="equipo">Equipo / Herramientas</option>
                <option value="marketing">Marketing / Publicidad</option>
                <option value="otros">Otros</option>
            </select>
        </div>

        {{-- Descripción --}}
        <div>
            <label style="font-size:.72rem;color:#9ca3af;display:block;margin-bottom:.3rem;">Descripción</label>
            <input wire:model="expenseDescription" type="text" placeholder="Descripción del egreso…"
                style="width:100%;padding:.625rem .75rem;font-size:.875rem;border-radius:.625rem;
                       border:1px solid #374151;background:#111827;color:#fff;box-sizing:border-box;"/>
        </div>

        {{-- Monto --}}
        <div>
            <label style="font-size:.72rem;color:#9ca3af;display:block;margin-bottom:.3rem;">Monto (Bs)</label>
            <input wire:model.live="expenseAmount" type="number" min="0" step="0.5" placeholder="0.00"
                style="width:100%;padding:.875rem;font-size:2rem;font-weight:700;text-align:center;
                       border-radius:.75rem;border:2px solid #374151;background:#111827;color:#fff;box-sizing:border-box;"/>
        </div>

        {{-- Método de pago --}}
        <div>
            <label style="font-size:.72rem;color:#9ca3af;display:block;margin-bottom:.4rem;">Método de pago</label>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;">
                @foreach(['cash' => 'Efectivo', 'transfer' => 'Transfer.', 'card' => 'Tarjeta'] as $m => $lbl)
                    <button wire:click="$set('expensePaymentMethod', '{{ $m }}')"
                        style="padding:.5rem .25rem;font-size:.75rem;font-weight:600;border-radius:.5rem;
                               border:2px solid {{ $expensePaymentMethod === $m ? '#f97316' : '#374151' }};
                               background:{{ $expensePaymentMethod === $m ? '#f97316' : 'transparent' }};
                               color:{{ $expensePaymentMethod === $m ? '#fff' : '#9ca3af' }};
                               cursor:pointer;touch-action:manipulation;">
                        {{ $lbl }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Botón guardar — NARANJA (≠ ámbar del cobrar, ≠ rojo de caja) --}}
        <button wire:click="registerExpense"
            style="width:100%;padding:.9rem;background:#f97316;color:#fff;font-weight:700;font-size:1rem;
                   border-radius:.875rem;border:none;cursor:pointer;touch-action:manipulation;
                   box-shadow:0 4px 12px rgba(249,115,22,.4);margin-top:.25rem;">
            Guardar Egreso — Bs {{ number_format($expenseAmount, 2) }}
        </button>

    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════
     MODAL: CANJEAR PREMIO DE FIDELIDAD
     ═══════════════════════════════════════════════════════ --}}
@if($showRedeemModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:100;
            display:flex;align-items:flex-end;justify-content:center;padding:0 12px 12px;"
     wire:click.self="$set('showRedeemModal', false)">
    <div style="background:#1f2937;border-radius:1.125rem;width:100%;max-width:24rem;
                padding:1.375rem;display:flex;flex-direction:column;gap:.875rem;">

        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h3 style="font-weight:700;font-size:1rem;color:#fff;display:flex;align-items:center;gap:.5rem;">
                &#11088; Canjear Premio
            </h3>
            <button wire:click="$set('showRedeemModal', false)"
                style="color:#9ca3af;font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>

        <p style="font-size:.78rem;color:#9ca3af;">
            El cliente tiene <strong style="color:#fbbf24;">{{ $customerLoyaltyPoints }} puntos</strong>.
            Selecciona el premio a canjear:
        </p>

        <div style="display:flex;flex-direction:column;gap:.5rem;">
            @foreach($this->getAvailableRewards() as $reward)
                <button wire:click="$set('selectedRewardId', {{ $reward->id }})"
                    style="padding:.75rem 1rem;border-radius:.75rem;border:2px solid
                           {{ $selectedRewardId == $reward->id ? '#f59e0b' : '#374151' }};
                           background:{{ $selectedRewardId == $reward->id ? 'rgba(245,158,11,.15)' : 'transparent' }};
                           color:#fff;text-align:left;cursor:pointer;touch-action:manipulation;
                           display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
                    <div>
                        <p style="font-weight:700;font-size:.9rem;">{{ $reward->name }}</p>
                        @if($reward->description)
                            <p style="font-size:.72rem;color:#9ca3af;margin-top:.125rem;">{{ $reward->description }}</p>
                        @endif
                    </div>
                    <span style="font-size:.72rem;font-weight:700;background:#f59e0b;color:#fff;
                                 padding:.25rem .625rem;border-radius:.375rem;flex-shrink:0;white-space:nowrap;">
                        {{ $reward->points_required }} pts
                    </span>
                </button>
            @endforeach
        </div>

        <button wire:click="redeemReward"
            @if(!$selectedRewardId) disabled @endif
            style="width:100%;padding:.9rem;font-weight:700;font-size:1rem;border-radius:.875rem;border:none;
                   cursor:{{ $selectedRewardId ? 'pointer' : 'not-allowed' }};touch-action:manipulation;
                   background:{{ $selectedRewardId ? '#f59e0b' : '#374151' }};
                   color:{{ $selectedRewardId ? '#fff' : '#6b7280' }};
                   box-shadow:{{ $selectedRewardId ? '0 4px 12px rgba(245,158,11,.4)' : 'none' }};">
            Confirmar Canje
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
    class="grid grid-cols-1 lg:grid-cols-3 gap-4"
    style="{{ $hasMobileNav
        ? 'padding-bottom: calc(7.5rem + env(safe-area-inset-bottom, 0px));'
        : 'padding-bottom: 5rem;' }}"
>

    {{-- ══ PANEL IZQUIERDO: Catálogo ══ --}}
    <div
        class="lg:col-span-2 space-y-3"
        :class="tab === 'catalog' ? 'block' : 'hidden lg:block'"
    >
        {{-- ── Estado de Caja ───────────────────────────────── --}}
        <div style="background:#1f2937;border-radius:.875rem;padding:.875rem .75rem;
                    display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
            @if($hasOpenRegister)
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <span style="width:10px;height:10px;border-radius:50%;background:#10b981;
                                 display:inline-block;animation:pulse 2s infinite;"></span>
                    <span style="font-size:.85rem;font-weight:600;color:#34d399;">Caja Abierta</span>
                </div>
                <button wire:click="$set('showCloseRegisterModal', true)"
                    style="font-size:.75rem;font-weight:600;color:#f87171;background:none;border:none;
                           cursor:pointer;touch-action:manipulation;padding:0;">
                    Cerrar Caja →
                </button>
            @else
                <div style="display:flex;align-items:center;gap:.5rem;flex:1;min-width:0;">
                    <span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block;flex-shrink:0;"></span>
                    <span style="font-size:.85rem;font-weight:600;color:#f87171;">Caja Cerrada</span>
                    <span style="font-size:.7rem;color:#6b7280;truncate;">· Ventas bloqueadas</span>
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0;align-items:center;">
                    @if($lastClosedRegisterId)
                        <a href="{{ route('ticket.cierre', $lastClosedRegisterId) }}" target="_blank"
                            style="font-size:.7rem;font-weight:600;color:#a78bfa;background:none;border:none;
                                   cursor:pointer;text-decoration:none;white-space:nowrap;">
                            🖨 Ticket
                        </a>
                    @endif
                    <button wire:click="$set('showOpenRegisterModal', true)"
                        style="font-size:.75rem;font-weight:700;background:#10b981;color:#fff;
                               border:none;border-radius:.5rem;padding:.375rem .75rem;cursor:pointer;
                               touch-action:manipulation;white-space:nowrap;">
                        + Abrir Caja
                    </button>
                </div>
            @endif
        </div>

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

            {{-- Badge tipo de cliente (solo si tiene tipo asignado) --}}
            @if($customerId && $customerTypeName)
            @php
                $typeBg     = match($customerTypeColor) {
                    'amber'  => '#fef3c7', 'purple' => '#f3e8ff', 'blue' => '#dbeafe',
                    'green'  => '#d1fae5', 'red'    => '#fee2e2', default => '#f3f4f6',
                };
                $typeText   = match($customerTypeColor) {
                    'amber'  => '#92400e', 'purple' => '#6b21a8', 'blue' => '#1e40af',
                    'green'  => '#065f46', 'red'    => '#991b1b', default => '#374151',
                };
                $typeBorder = match($customerTypeColor) {
                    'amber'  => '#f59e0b', 'purple' => '#a855f7', 'blue' => '#3b82f6',
                    'green'  => '#10b981', 'red'    => '#ef4444', default => '#9ca3af',
                };
            @endphp
            <div style="margin-top:.625rem;border-radius:.625rem;padding:.5rem .75rem;
                        border:2px solid {{ $typeBorder }};background:{{ $typeBg }};
                        display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                <div>
                    <span style="font-size:.82rem;font-weight:700;color:{{ $typeText }};">
                        {{ $customerTypeName }}
                    </span>
                    @if($customerTypeDiscount > 0)
                        <span style="font-size:.72rem;color:{{ $typeText }};margin-left:.25rem;">
                            — {{ $customerTypeDiscount }}% desc.
                        </span>
                    @endif
                </div>
                <span style="font-size:.68rem;font-weight:700;padding:.2rem .5rem;border-radius:.375rem;white-space:nowrap;
                             background:{{ $customerTypeCostBearer === 'business' ? '#1d4ed8' : '#dc2626' }};color:#fff;">
                    {{ $customerTypeCostBearer === 'business' ? 'Costo: negocio' : 'Costo: barbero' }}
                </span>
            </div>
            @endif

            {{-- Tarjeta de fidelidad (solo si el cliente está identificado) --}}
            @if($customerId && $customerPhone)
                <div style="margin-top:.75rem;border-radius:.625rem;padding:.625rem .75rem;
                            background:linear-gradient(135deg,#1e3a5f 0%,#1a3a6a 100%);
                            display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span style="font-size:1.1rem;">&#11088;</span>
                        <div>
                            <p style="font-size:.7rem;color:#93c5fd;font-weight:600;text-transform:uppercase;
                                      letter-spacing:.05em;line-height:1;">Puntos acumulados</p>
                            <p style="font-size:1.25rem;font-weight:800;color:#fff;line-height:1.2;">
                                {{ $customerLoyaltyPoints }}
                                <span style="font-size:.7rem;font-weight:400;color:#93c5fd;">pts</span>
                            </p>
                        </div>
                    </div>
                    @if($customerIsBirthday)
                        <span style="font-size:.7rem;font-weight:700;background:#fde68a;color:#92400e;
                                     padding:.25rem .5rem;border-radius:.375rem;">
                            🎂 ¡Cumpleaños!
                        </span>
                    @endif
                    @php $redeemable = $this->getAvailableRewards(); @endphp
                    @if($redeemable->isNotEmpty())
                        <button wire:click="$set('showRedeemModal', true)"
                            style="font-size:.72rem;font-weight:700;background:#f59e0b;color:#fff;
                                   padding:.375rem .75rem;border-radius:.5rem;border:none;cursor:pointer;
                                   touch-action:manipulation;white-space:nowrap;flex-shrink:0;">
                            Canjear premio
                        </button>
                    @endif
                </div>
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
                    {{-- Descuento: auto desde tipo de cliente (read-only) o manual para cajero --}}
                    @if(!$isBarbero || $customerTypeDiscount > 0)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-300">
                            @if($customerTypeDiscount > 0)
                                Desc. ({{ $customerTypeName }}):
                            @else
                                Descuento:
                            @endif
                        </span>
                        @if($customerTypeDiscount > 0)
                            <span style="color:#ef4444;font-weight:600;">
                                &minus; Bs {{ number_format($discount, 2) }}
                            </span>
                        @else
                            <input wire:model.live="discount" type="number" min="0" step="0.5"
                                class="w-24 text-right px-2 py-1 text-sm border rounded-lg dark:bg-gray-700
                                       dark:border-gray-600 dark:text-white focus:ring-1 focus:ring-amber-400"/>
                        @endif
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-xl text-gray-900 dark:text-white pt-1">
                        <span>TOTAL:</span>
                        <span class="text-amber-500">Bs {{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>Comision barbero:</span>
                        <span>
                            Bs {{ number_format($totalCommission, 2) }}
                            @if($customerTypeCostBearer === 'barber' && $customerTypeDiscount > 0)
                                <span style="color:#f87171;">&nbsp;(−{{ $customerTypeDiscount }}%)</span>
                            @elseif($customerTypeCostBearer === 'business' && $customerTypeDiscount > 0)
                                <span style="color:#34d399;">&nbsp;(neg. asume)</span>
                            @endif
                        </span>
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

            {{-- BOTÓN COBRAR — bloqueado si la caja está cerrada --}}
            @if($hasOpenRegister)
                <button wire:click="processSale"
                    wire:confirm="Confirmar venta por Bs {{ number_format($total, 2) }}?"
                    style="width:100%;padding:1rem;background:#f59e0b;color:#fff;font-weight:700;
                           font-size:1.25rem;border-radius:1rem;border:none;cursor:pointer;
                           box-shadow:0 4px 12px rgba(245,158,11,.4);touch-action:manipulation;
                           transition:transform .1s;active:transform:scale(.97);">
                    Cobrar Bs {{ number_format($total, 2) }}
                </button>
            @else
                <div style="width:100%;padding:1rem;background:#374151;color:#9ca3af;font-weight:700;
                            font-size:1rem;border-radius:1rem;text-align:center;user-select:none;">
                    🔒 Abre la caja para cobrar
                </div>
            @endif

        </div>
        @endif

    </div>{{-- fin panel derecho --}}

    {{-- ════════════════════════════════════════════════════
         FAB FLOTANTE: Registrar Egreso
         Posicionado encima del tab bar + mobile nav
         ════════════════════════════════════════════════════ --}}
    <button wire:click="$set('showExpenseModal', true)"
        title="Registrar Egreso"
        class="lg:hidden"
        style="position:fixed;right:16px;z-index:45;
               width:50px;height:50px;border-radius:50%;
               background:#ef4444;color:#fff;border:none;cursor:pointer;
               display:flex;align-items:center;justify-content:center;
               box-shadow:0 4px 16px rgba(239,68,68,.5);touch-action:manipulation;
               {{ $hasMobileNav
                   ? 'bottom:calc(7rem + env(safe-area-inset-bottom,0px));'
                   : 'bottom:calc(3.5rem + env(safe-area-inset-bottom,0px));' }}">
        <svg style="width:22px;height:22px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
        </svg>
    </button>

    {{-- ════════════════════════════════════════════════════
         BARRA INFERIOR MÓVIL — Tab Catálogo / Carrito
         Inline styles garantizan el layout sin npm build
         ════════════════════════════════════════════════════ --}}
    <div class="lg:hidden dark:bg-gray-900 dark:border-gray-700"
         style="position:fixed;left:0;right:0;z-index:40;
                display:grid;grid-template-columns:1fr 1fr;
                background:white;border-top:1px solid #e5e7eb;
                {{ $hasMobileNav
                    ? 'bottom:calc(3.5rem + env(safe-area-inset-bottom,0px));'
                    : 'bottom:0;' }}">

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
