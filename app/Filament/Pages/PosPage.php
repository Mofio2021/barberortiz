<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Expense;
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
    public string  $customerName     = 'Cliente General';
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

    // Comprobante QR — se sube como archivo temporal Livewire
    public $qrReceipt = null;

    // ── Egreso rápido desde el POS ──────────────────────────────
    public bool   $showExpenseModal     = false;
    public string $expenseCategory      = 'otros';
    public string $expenseDescription   = '';
    public float  $expenseAmount        = 0;
    public string $expensePaymentMethod = 'cash';

    // ────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $branches = Branch::where('is_active', true)->get();
        if ($branches->count() === 1) {
            $this->selectedBranchId = $branches->first()->id;
        }

        $user = Auth::user();
        if ($user instanceof User && $user->hasRole('barbero')) {
            $staff = Staff::where('user_id', $user->id)->first();
            if ($staff) {
                $this->selectedStaffId  = $staff->id;
                $this->selectedBranchId = $staff->branch_id;
            }
        }
    }

    public function getBranches()
    {
        return Branch::where('is_active', true)->get();
    }

    public function getStaffList()
    {
        if (! $this->selectedBranchId) {
            return collect();
        }
        return Staff::where('branch_id', $this->selectedBranchId)
            ->where('status', 'active')
            ->get();
    }

    public function getServices()
    {
        return Service::where('is_active', true)
            ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', "%{$this->searchTerm}%"))
            ->orderBy('name')
            ->get();
    }

    public function getProducts()
    {
        return Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', "%{$this->searchTerm}%"))
            ->orderBy('name')
            ->get();
    }

    public function addService(int $serviceId): void
    {
        if (! $this->selectedStaffId) {
            Notification::make()->title('Selecciona un barbero primero')->warning()->send();
            return;
        }
        $service = Service::find($serviceId);
        if (! $service) {
            return;
        }
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
        if (! $product) {
            return;
        }
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
        $this->total           = max(0, $this->subtotal - $this->discount);
        $this->totalCommission = collect($this->cartItems)->sum(fn ($i) => $i['commission']);
        $this->change          = max(0, $this->amountPaid - $this->total);
    }

    public function lookupCustomer(): void
    {
        if (strlen($this->customerPhone) < 6) {
            return;
        }
        $customer = Customer::where('phone', $this->customerPhone)->first();
        if ($customer) {
            $this->customerName = $customer->name;
            $this->customerId   = $customer->id;
            Notification::make()
                ->title("Cliente: {$customer->name}")
                ->body("Visitas: {$customer->total_visits}")
                ->success()
                ->send();
        }
    }

    public function processSale(): void
    {
        if (empty($this->cartItems)) {
            Notification::make()->title('El carrito esta vacio')->warning()->send();
            return;
        }
        if (! $this->selectedStaffId || ! $this->selectedBranchId) {
            Notification::make()->title('Selecciona sucursal y barbero')->warning()->send();
            return;
        }

        // Validar comprobante QR obligatorio
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
            $customer = null;
            if ($this->customerPhone) {
                $customer = Customer::firstOrCreate(
                    ['phone' => $this->customerPhone],
                    ['name' => $this->customerName ?: 'Cliente General', 'branch_id' => $this->selectedBranchId]
                );
                $customer->increment('total_visits');
                $customer->update(['last_visit' => now()]);
            }

            // Guardar comprobante QR en carpeta organizada por fecha
            $qrReceiptPath = null;
            if ($this->paymentMethod === 'qr' && $this->qrReceipt) {
                $qrReceiptPath = $this->qrReceipt->store(
                    'comprobantes/' . today()->format('Y-m-d'),
                    'public'
                );
            }

            $sale = Sale::create([
                'branch_id'        => $this->selectedBranchId,
                'customer_id'      => $customer?->id,
                'staff_id'         => $this->selectedStaffId,
                'cashier_id'       => Auth::id(),
                'subtotal'         => $this->subtotal,
                'discount'         => $this->discount,
                'total'            => $this->total,
                'total_commission' => $this->totalCommission,
                'payment_method'   => $this->paymentMethod,
                'amount_paid'      => $this->amountPaid ?: $this->total,
                'change_given'     => $this->change,
                'notes'            => $this->notes,
                'qr_receipt_path'  => $qrReceiptPath,
            ]);

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
            }

            DB::commit();
            Notification::make()
                ->title('Venta registrada')
                ->body('Total: Bs ' . number_format($this->total, 2) . ' | Cambio: Bs ' . number_format($this->change, 2))
                ->success()
                ->send();
            $this->resetCart();

        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }

    public function registerExpense(): void
    {
        if (! $this->expenseDescription || $this->expenseAmount <= 0) {
            Notification::make()->title('Completa descripcion y monto')->warning()->send();
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

        $this->showExpenseModal     = false;
        $this->expenseDescription   = '';
        $this->expenseAmount        = 0;
        $this->expenseCategory      = 'otros';
        $this->expensePaymentMethod = 'cash';
    }

    public function resetCart(): void
    {
        $this->cartItems       = [];
        $this->customerPhone   = '';
        $this->customerName    = 'Cliente General';
        $this->customerId      = null;
        $this->paymentMethod   = 'cash';
        $this->amountPaid      = 0;
        $this->discount        = 0;
        $this->notes           = '';
        $this->subtotal        = 0;
        $this->total           = 0;
        $this->change          = 0;
        $this->totalCommission = 0;
        $this->searchTerm      = '';
        $this->qrReceipt       = null;
    }

    public function updatedAmountPaid(): void { $this->recalculate(); }
    public function updatedDiscount(): void   { $this->recalculate(); }

    // Cuando cambia el método de pago, limpiar campos anteriores
    public function updatedPaymentMethod(): void
    {
        $this->qrReceipt  = null;
        $this->amountPaid = 0;
        $this->change     = 0;
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }
}
