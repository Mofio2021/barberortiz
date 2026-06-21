<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyReward;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithFileUploads;

class PosPage extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static string  $view            = 'filament.pages.pos-page';
    protected static ?string $navigationLabel = 'Punto de Venta (POS)';
    protected static ?string $title           = 'Punto de Venta';
    protected static ?string $slug            = 'pos';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationGroup = 'Operaciones';

    // ── Estado del POS ──────────────────────────────────────────
    public ?int    $selectedBranchId = null;
    public ?int    $selectedStaffId  = null;
    public array   $cartItems        = [];
    public string  $customerPhone    = '';
    public string  $customerName     = '';
    public ?int    $customerId       = null;
    public string  $paymentMethod    = 'cash';
    public float   $amountPaid       = 0;
    public float   $discount         = 0;
    public string  $notes            = '';
    public string  $searchTerm       = '';
    public string  $activeTab        = 'services';

    public float   $subtotal         = 0;
    public float   $total            = 0;
    public float   $change           = 0;
    public float   $totalCommission  = 0;

    // Comprobante QR (archivo temporal Livewire)
    public $qrReceipt = null;

    // Visibilidad según rol
    public bool $isBarbero    = false;
    public bool $hasMobileNav = false;

    // ── Caja (CashRegister) ─────────────────────────────────────
    public ?int   $openRegisterId         = null;
    public ?int   $lastClosedRegisterId   = null;
    public bool   $hasOpenRegister        = false;
    public bool   $showOpenRegisterModal  = false;
    public bool   $showCloseRegisterModal = false;
    public float  $openingBalance         = 0;
    public float  $countedCash            = 0;
    public string $closeNotes             = '';

    // ── Fidelización ────────────────────────────────────────────
    public int    $customerLoyaltyPoints = 0;
    public bool   $customerIsBirthday   = false;
    public bool   $showRedeemModal      = false;
    public ?int   $selectedRewardId     = null;

    // ── Egreso rápido ───────────────────────────────────────────
    public bool   $showExpenseModal     = false;
    public string $expenseCategory      = 'otros';
    public string $expenseDescription   = '';
    public float  $expenseAmount        = 0;
    public string $expensePaymentMethod = 'cash';

    // ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        $this->isBarbero    = $user instanceof User && $user->hasRole('barbero');
        $this->hasMobileNav = $user instanceof User && $user->hasAnyRole(['barbero', 'cajero']);

        $branches = Branch::where('is_active', true)->get();
        if ($branches->count() === 1) {
            $this->selectedBranchId = $branches->first()->id;
        }

        // Barbero: sucursal y staff se asignan automáticamente
        if ($this->isBarbero) {
            $staff = Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $this->selectedStaffId  = $staff->id;
                $this->selectedBranchId = $staff->branch_id;
            }
        }

        $this->loadRegister();
    }

    // ── Caja ────────────────────────────────────────────────────

    public function loadRegister(): void
    {
        if (! $this->selectedBranchId) {
            $this->openRegisterId  = null;
            $this->hasOpenRegister = false;
            return;
        }

        $register = CashRegister::where('branch_id', $this->selectedBranchId)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $this->openRegisterId  = $register?->id;
        $this->hasOpenRegister = $register !== null;
    }

    public function openRegister(): void
    {
        if (! $this->selectedBranchId) {
            Notification::make()->title('Selecciona una sucursal primero')->warning()->send();
            return;
        }

        // Si ya existe una caja abierta, la usamos
        $existing = CashRegister::where('branch_id', $this->selectedBranchId)
            ->where('status', 'open')
            ->first();

        if ($existing) {
            $this->openRegisterId  = $existing->id;
            $this->hasOpenRegister = true;
            $this->showOpenRegisterModal = false;
            Notification::make()->title('Caja ya estaba abierta')->info()->send();
            return;
        }

        $register = CashRegister::create([
            'branch_id'       => $this->selectedBranchId,
            'user_id'         => Auth::id(),
            'status'          => 'open',
            'opening_balance' => $this->openingBalance,
            'opened_at'       => now(),
        ]);

        $this->openRegisterId  = $register->id;
        $this->hasOpenRegister = true;
        $this->openingBalance  = 0;
        $this->showOpenRegisterModal = false;

        Notification::make()
            ->title('¡Caja abierta!')
            ->body('Saldo inicial: Bs ' . number_format($register->opening_balance, 2))
            ->success()
            ->send();
    }

    public function closeRegister(): void
    {
        if (! $this->openRegisterId) return;

        $register = CashRegister::find($this->openRegisterId);
        if (! $register) return;

        // Calcular totales desde la apertura
        $salesQuery = Sale::where('branch_id', $this->selectedBranchId)
            ->where('created_at', '>=', $register->opened_at);

        $cashSales = (float) (clone $salesQuery)->where('payment_method', 'cash')->sum('total');
        $qrSales   = (float) (clone $salesQuery)->where('payment_method', 'qr')->sum('total');

        $expenses = (float) Expense::where('branch_id', $this->selectedBranchId)
            ->where('created_at', '>=', $register->opened_at)
            ->sum('amount');

        $expectedCash   = (float) $register->opening_balance + $cashSales - $expenses;
        $cashDifference = $this->countedCash - $expectedCash;

        $register->update([
            'status'           => 'closed',
            'closing_balance'  => $this->countedCash,
            'total_cash_sales' => $cashSales,
            'total_qr_sales'   => $qrSales,
            'total_expenses'   => $expenses,
            'notes'            => $this->closeNotes,
            'closed_at'        => now(),
        ]);

        $this->lastClosedRegisterId   = $register->id;
        $this->openRegisterId         = null;
        $this->hasOpenRegister        = false;
        $this->showCloseRegisterModal = false;
        $this->countedCash            = 0;
        $this->closeNotes             = '';

        Notification::make()
            ->title('Caja cerrada')
            ->body(
                'Ventas efectivo: Bs ' . number_format($cashSales, 2)
                . ' | QR: Bs ' . number_format($qrSales, 2)
                . ' | Diferencia: Bs ' . number_format($cashDifference, 2)
            )
            ->success()
            ->send();
    }

    // Cuando el cajero cambia de sucursal, recarga la caja
    public function updatedSelectedBranchId(): void
    {
        $this->loadRegister();
    }

    // ── Catálogo ────────────────────────────────────────────────

    public function getBranches()
    {
        return Branch::where('is_active', true)->get();
    }

    public function getStaffList()
    {
        if (! $this->selectedBranchId) return collect();

        return Staff::where('branch_id', $this->selectedBranchId)
            ->where('status', 'active')
            ->get();
    }

    public function getServices()
    {
        return Service::active()
            ->forBranch($this->selectedBranchId)
            ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', "%{$this->searchTerm}%"))
            ->orderBy('name')
            ->get();
    }

    public function getProducts()
    {
        return Product::active()
            ->forBranch($this->selectedBranchId)
            ->where('stock', '>', 0)
            ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', "%{$this->searchTerm}%"))
            ->orderBy('name')
            ->get();
    }

    // ── Carrito ─────────────────────────────────────────────────

    public function addService(int $serviceId): void
    {
        if (! $this->selectedStaffId) {
            Notification::make()->title('Selecciona un barbero primero')->warning()->send();
            return;
        }
        $service = Service::find($serviceId);
        if (! $service) return;

        $staff      = Staff::find($this->selectedStaffId);
        $commission = $staff->calculateCommission($service->price);
        $key        = "service_{$serviceId}";

        if (isset($this->cartItems[$key])) {
            $this->cartItems[$key]['quantity']++;
            $this->cartItems[$key]['commission'] += $commission;
        } else {
            $this->cartItems[$key] = [
                'key'        => $key,
                'type'       => 'service',
                'id'         => $serviceId,
                'name'       => $service->name,
                'price'      => $service->price,
                'quantity'   => 1,
                'commission' => $commission,
            ];
        }
        $this->recalculate();
    }

    public function addProduct(int $productId): void
    {
        if (! $this->selectedStaffId) {
            Notification::make()->title('Selecciona un barbero primero')->warning()->send();
            return;
        }
        $product    = Product::find($productId);
        if (! $product) return;

        $key        = "product_{$productId}";
        $currentQty = $this->cartItems[$key]['quantity'] ?? 0;

        if ($currentQty >= $product->stock) {
            Notification::make()->title("Sin stock: {$product->name}")->danger()->send();
            return;
        }
        $commission = $product->calculateCommission();

        if (isset($this->cartItems[$key])) {
            $this->cartItems[$key]['quantity']++;
            $this->cartItems[$key]['commission'] += $commission;
        } else {
            $this->cartItems[$key] = [
                'key'        => $key,
                'type'       => 'product',
                'id'         => $productId,
                'name'       => $product->name,
                'price'      => $product->price,
                'quantity'   => 1,
                'commission' => $commission,
            ];
        }
        $this->recalculate();
    }

    public function removeItem(string $key): void
    {
        unset($this->cartItems[$key]);
        $this->recalculate();
    }

    public function recalculate(): void
    {
        $this->subtotal        = collect($this->cartItems)->sum(fn ($i) => $i['price'] * $i['quantity']);
        // Barbero no aplica descuentos
        if ($this->isBarbero) $this->discount = 0;
        $this->total           = max(0, $this->subtotal - $this->discount);
        $this->totalCommission = collect($this->cartItems)->sum(fn ($i) => $i['commission']);
        $this->change          = max(0, $this->amountPaid - $this->total);
    }

    public function lookupCustomer(): void
    {
        if (strlen($this->customerPhone) < 6) return;

        $customer = Customer::where('phone', $this->customerPhone)->first();
        if ($customer) {
            $this->customerName          = $customer->name;
            $this->customerId            = $customer->id;
            $this->customerLoyaltyPoints = (int) $customer->loyalty_points;
            $this->customerIsBirthday    = $customer->isBirthday();

            $body = "Visitas: {$customer->total_visits} · Puntos: {$customer->loyalty_points}";
            if ($this->customerIsBirthday) {
                $body .= ' · 🎂 ¡Hoy es su cumpleaños!';
            }

            Notification::make()
                ->title("Cliente: {$customer->name}")
                ->body($body)
                ->success()
                ->send();
        }
    }

    // ── Fidelización ─────────────────────────────────────────────

    public function getAvailableRewards(): \Illuminate\Support\Collection
    {
        if (! $this->customerId || $this->customerLoyaltyPoints <= 0) {
            return collect();
        }
        return LoyaltyReward::active()
            ->where('points_required', '<=', $this->customerLoyaltyPoints)
            ->get();
    }

    public function redeemReward(): void
    {
        if (! $this->customerId || ! $this->selectedRewardId) {
            return;
        }

        $customer = Customer::find($this->customerId);
        $reward   = LoyaltyReward::find($this->selectedRewardId);

        if (! $customer || ! $reward) {
            return;
        }

        if ($customer->loyalty_points < $reward->points_required) {
            Notification::make()
                ->title('Puntos insuficientes')
                ->body("Necesita {$reward->points_required} pts, tiene {$customer->loyalty_points}.")
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () use ($customer, $reward) {
            $customer->decrement('loyalty_points', $reward->points_required);

            LoyaltyRedemption::create([
                'customer_id'       => $customer->id,
                'loyalty_reward_id' => $reward->id,
                'points_spent'      => $reward->points_required,
                'redeemed_by'       => Auth::id(),
                'redeemed_at'       => now(),
            ]);
        });

        $this->customerLoyaltyPoints = (int) $customer->fresh()->loyalty_points;
        $this->showRedeemModal       = false;
        $this->selectedRewardId      = null;

        Notification::make()
            ->title("Premio canjeado: {$reward->name}")
            ->body("Quedan {$this->customerLoyaltyPoints} puntos.")
            ->success()
            ->send();
    }

    // ── Venta ───────────────────────────────────────────────────

    public function processSale(): void
    {
        if (empty($this->cartItems)) {
            Notification::make()->title('El carrito está vacío')->warning()->send();
            return;
        }
        if (! $this->selectedStaffId || ! $this->selectedBranchId) {
            Notification::make()->title('Selecciona sucursal y barbero')->warning()->send();
            return;
        }

        // Verificar caja abierta
        if (! $this->hasOpenRegister) {
            Notification::make()
                ->title('Caja cerrada')
                ->body('Debe abrir caja antes de realizar ventas.')
                ->danger()
                ->send();
            return;
        }

        // Comprobante QR obligatorio
        if ($this->paymentMethod === 'qr' && ! $this->qrReceipt) {
            Notification::make()
                ->title('Comprobante QR obligatorio')
                ->body('Adjunta la foto del comprobante para continuar.')
                ->warning()
                ->send();
            return;
        }

        DB::beginTransaction();
        try {
            // ── Resolver cliente ────────────────────────────────────
            if ($this->customerId) {
                // Cliente ya identificado por teléfono
                $customer = Customer::find($this->customerId);
                $customer?->increment('total_visits');
                $customer?->update(['last_visit' => now()]);
            } elseif ($this->customerPhone) {
                // Teléfono ingresado pero cliente no encontrado → crear
                $customer = Customer::firstOrCreate(
                    ['phone' => $this->customerPhone],
                    ['name' => filled($this->customerName) ? $this->customerName : 'Cliente General',
                     'branch_id' => $this->selectedBranchId]
                );
                $customer->increment('total_visits');
                $customer->update(['last_visit' => now()]);
            } else {
                // Sin datos → buscar/crear "Cliente General" para esta sucursal
                $customer = Customer::firstOrCreate(
                    ['name' => 'Cliente General', 'branch_id' => $this->selectedBranchId, 'phone' => null],
                );
            }

            // ── Comprobante QR ──────────────────────────────────────
            $qrReceiptPath = null;
            if ($this->paymentMethod === 'qr' && $this->qrReceipt) {
                $qrReceiptPath = $this->qrReceipt->store(
                    'comprobantes/' . today()->format('Y-m-d'),
                    'public'
                );
            }

            // ── Crear venta ─────────────────────────────────────────
            $sale = Sale::create([
                'branch_id'        => $this->selectedBranchId,
                'user_id'          => Auth::id(),   // columna legada NOT NULL
                'customer_id'      => $customer->id,
                'staff_id'         => $this->selectedStaffId,
                'cashier_id'       => Auth::id(),
                'subtotal'         => $this->subtotal,
                'discount'         => $this->isBarbero ? 0 : $this->discount,
                'total'            => $this->total,
                'total_commission' => $this->totalCommission,
                'payment_method'   => $this->paymentMethod,
                'amount_paid'      => $this->amountPaid ?: $this->total,
                'change_given'     => $this->change,
                'notes'            => $this->notes,
                'qr_receipt_path'  => $qrReceiptPath,
            ]);

            $hasService = false;
            foreach ($this->cartItems as $item) {
                SaleItem::create([
                    'sale_id'           => $sale->id,
                    'staff_id'          => $this->selectedStaffId,
                    'item_type'         => $item['type'],
                    'item_id'           => $item['id'],
                    'item_name'         => $item['name'],
                    'price_at_time'     => $item['price'],
                    'quantity'          => $item['quantity'],
                    'commission_amount' => $item['commission'],
                ]);
                if ($item['type'] === 'product') {
                    Product::find($item['id'])->decrement('stock', $item['quantity']);
                }
                if ($item['type'] === 'service') {
                    $hasService = true;
                }
            }

            // Sumar 1 punto de fidelidad si la venta incluye al menos un servicio
            // y el cliente es identificado (no el cliente general sin teléfono)
            if ($hasService && $this->customerId && $this->customerPhone) {
                $customer?->increment('loyalty_points');
                $this->customerLoyaltyPoints = (int) ($this->customerLoyaltyPoints + 1);
            }

            DB::commit();

            Notification::make()
                ->title('¡Venta registrada!')
                ->body(
                    'Total: Bs ' . number_format($this->total, 2)
                    . ($this->change > 0 ? ' | Cambio: Bs ' . number_format($this->change, 2) : '')
                )
                ->success()
                ->send();

            $this->resetCart();

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('Error al registrar venta')->body($e->getMessage())->danger()->send();
        }
    }

    // ── Egresos ─────────────────────────────────────────────────

    public function registerExpense(): void
    {
        if (! $this->expenseDescription || $this->expenseAmount <= 0) {
            Notification::make()->title('Completa descripción y monto')->warning()->send();
            return;
        }

        Expense::create([
            'user_id'        => Auth::id(),
            'branch_id'      => $this->selectedBranchId,
            'category'       => $this->expenseCategory,
            'description'    => $this->expenseDescription,
            'amount'         => $this->expenseAmount,
            'payment_method' => $this->expensePaymentMethod,
            'expense_date'   => today(),
        ]);

        Notification::make()
            ->title('Egreso registrado')
            ->body('Bs ' . number_format($this->expenseAmount, 2) . ' — ' . $this->expenseDescription)
            ->success()
            ->send();

        $this->showExpenseModal   = false;
        $this->expenseDescription = '';
        $this->expenseAmount      = 0;
        $this->expenseCategory    = 'otros';
        $this->expensePaymentMethod = 'cash';
    }

    // ── Reset ────────────────────────────────────────────────────

    public function resetCart(): void
    {
        $this->cartItems             = [];
        $this->customerPhone         = '';
        $this->customerName          = '';
        $this->customerId            = null;
        $this->customerLoyaltyPoints = 0;
        $this->customerIsBirthday    = false;
        $this->showRedeemModal       = false;
        $this->selectedRewardId      = null;
        $this->paymentMethod         = 'cash';
        $this->amountPaid            = 0;
        $this->discount              = 0;
        $this->notes                 = '';
        $this->subtotal              = 0;
        $this->total                 = 0;
        $this->change                = 0;
        $this->totalCommission       = 0;
        $this->qrReceipt             = null;
    }

    public function updatedAmountPaid(): void { $this->recalculate(); }
    public function updatedDiscount(): void   { $this->recalculate(); }

    public function updatedPaymentMethod(): void
    {
        $this->qrReceipt  = null;
        $this->amountPaid = 0;
        $this->change     = 0;
    }

    // ── Acceso ───────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }
}
