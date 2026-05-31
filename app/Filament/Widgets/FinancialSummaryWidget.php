<?php

namespace App\Filament\Widgets;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialSummaryWidget extends Widget
{
    protected static string $view = 'filament.widgets.financial-summary-widget';
    protected static ?int   $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public string $period           = 'month';
    public ?int   $selectedBranchId = null;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    public function mount(): void
    {
        $user = Auth::user();
        // admin_sucursal solo ve su sucursal — sin opción de cambiarla
        if ($user instanceof User && $user->hasRole('admin_sucursal') && $user->branch_id) {
            $this->selectedBranchId = $user->branch_id;
        }
    }

    // Livewire convierte el value del <select> a string; lo normalizamos
    public function updatedSelectedBranchId(mixed $value): void
    {
        $this->selectedBranchId = ($value === '' || $value === null) ? null : (int) $value;
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
    }

    protected function getViewData(): array
    {
        [$from, $to, $label] = match ($this->period) {
            'today' => [
                today()->startOfDay(),
                today()->endOfDay(),
                'Hoy — ' . today()->isoFormat('D [de] MMMM'),
            ],
            'week'  => [
                now()->startOfWeek(),
                now()->endOfWeek(),
                'Semana ' . now()->startOfWeek()->isoFormat('D') . '–' . now()->endOfWeek()->isoFormat('D [de] MMM'),
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->isoFormat('MMMM YYYY'),
            ],
        };

        $branchId = $this->selectedBranchId;

        // ── Ventas totales del período ───────────────────────────────
        $ventas = (float) Sale::whereBetween('created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total');

        $cantVentas = (int) Sale::whereBetween('created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->count();

        // ── Egresos del período ──────────────────────────────────────
        $egresos = (float) Expense::whereBetween('expense_date', [
            $from->toDateString(),
            $to->toDateString(),
        ])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        // ── Costo de productos vendidos (cost_price × qty) ───────────
        // JOIN sale_items → products para obtener el cost_price real
        $costoProductos = (float) DB::table('sale_items as si')
            ->join('products as p', function ($join) {
                $join->on('si.item_id', '=', 'p.id')
                     ->where('si.item_type', '=', 'product');
            })
            ->join('sales as s', 'si.sale_id', '=', 's.id')
            ->whereBetween('s.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('s.branch_id', $branchId))
            ->sum(DB::raw('si.quantity * p.cost_price'));

        // ── Comisiones pagadas al staff ──────────────────────────────
        $comisiones = (float) Sale::whereBetween('created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total_commission');

        // ── Ganancia Neta = Ventas − Comisiones − Egresos − Costo ───
        // Las comisiones se descuentan porque son un costo directo de la mano de obra
        $ganancia       = $ventas - $comisiones - $egresos - $costoProductos;
        $margenPct      = $ventas > 0 ? round(($ganancia / $ventas) * 100, 1) : 0;
        $ticketPromedio = $cantVentas > 0 ? round($ventas / $cantVentas, 2) : 0;

        // ── Período anterior para comparación ────────────────────────
        [$prevFrom, $prevTo] = match ($this->period) {
            'today' => [today()->subDay()->startOfDay(), today()->subDay()->endOfDay()],
            'week'  => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            default => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };

        $ventasAnterior = (float) Sale::whereBetween('created_at', [$prevFrom, $prevTo])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('total');

        $tendencia    = $ventas >= $ventasAnterior ? 'up' : 'down';
        $diffVentas   = $ventas - $ventasAnterior;

        // ── Datos para el filtro de sucursal ────────────────────────
        $authUser     = Auth::user();
        $isSuperAdmin = $authUser instanceof User && $authUser->hasRole('super_admin');
        $branches     = $isSuperAdmin ? Branch::where('is_active', true)->get() : collect();

        $selectedBranchName = $branchId
            ? (Branch::find($branchId)?->name ?? '—')
            : 'Todas las sucursales';

        // ── Reporte por barbero: ventas + comisión acumulada ─────────
        // Filtra por período y sucursal; ordenado por mayor comisión primero
        $staffStats = DB::table('sales as s')
            ->join('staff as st', 's.staff_id', '=', 'st.id')
            ->whereBetween('s.created_at', [$from, $to])
            ->when($branchId, fn ($q) => $q->where('s.branch_id', $branchId))
            ->select([
                'st.id',
                'st.name',
                DB::raw('COUNT(s.id)               as total_ventas'),
                DB::raw('SUM(s.total)              as total_facturado'),
                DB::raw('SUM(s.total_commission)   as total_comision'),
            ])
            ->groupBy('st.id', 'st.name')
            ->orderByDesc('total_comision')
            ->get();

        return compact(
            'ventas', 'egresos', 'costoProductos', 'comisiones',
            'ganancia', 'margenPct', 'ticketPromedio',
            'cantVentas', 'label', 'tendencia', 'diffVentas',
            'branches', 'selectedBranchName', 'isSuperAdmin',
            'staffStats'
        );
    }
}
