<?php

namespace App\Filament\Pages;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommissionReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-chart-bar-square';
    protected static string  $view            = 'filament.pages.commission-report';
    protected static ?string $title           = 'Reportes Generales';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $slug            = 'reportes';
    protected static ?string $navigationGroup = 'Operaciones';
    protected static ?int    $navigationSort  = 40;

    public string $desde  = '';
    public string $hasta  = '';
    public string $periodo = 'mes';

    public function mount(): void
    {
        $this->desde = now()->startOfMonth()->toDateString();
        $this->hasta = today()->toDateString();
    }

    public function setPeriodo(string $p): void
    {
        $this->periodo = $p;

        [$this->desde, $this->hasta] = match ($p) {
            'hoy'   => [today()->toDateString(), today()->toDateString()],
            'semana' => [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ],
            'mes'   => [
                now()->startOfMonth()->toDateString(),
                today()->toDateString(),
            ],
            default => [$this->desde, $this->hasta],
        };
    }

    // ── Helpers de rango ────────────────────────────────────────────

    private function rangoDatetime(): array
    {
        return ["{$this->desde} 00:00:00", "{$this->hasta} 23:59:59"];
    }

    private function rangoFecha(): array
    {
        return [$this->desde, $this->hasta];
    }

    // ── Computed properties — se recalculan al cambiar desde/hasta ──

    #[Computed]
    public function resumen(): array
    {
        [$from, $to] = $this->rangoDatetime();
        [$fd, $td]   = $this->rangoFecha();

        $ventas     = (float) Sale::whereBetween('created_at', [$from, $to])->sum('total');
        $egresos    = (float) Expense::whereBetween('expense_date', [$fd, $td])->sum('amount');
        $comisiones = (float) SaleItem::whereBetween('created_at', [$from, $to])->sum('commission_amount');
        $cantidad   = (int)   Sale::whereBetween('created_at', [$from, $to])->count();

        return [
            'ventas'     => $ventas,
            'egresos'    => $egresos,
            'comisiones' => $comisiones,
            'neto'       => $ventas - $egresos - $comisiones,
            'cantidad'   => $cantidad,
        ];
    }

    #[Computed]
    public function ventasPorMetodo(): Collection
    {
        [$from, $to] = $this->rangoDatetime();

        $rows = Sale::whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_method, COUNT(*) as cantidad, SUM(total) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $totalGeneral = $rows->sum('total') ?: 1;

        return $rows->map(function ($row) use ($totalGeneral) {
            $row->porcentaje = round(($row->total / $totalGeneral) * 100, 1);
            $row->label = match ($row->payment_method) {
                'cash'     => 'Efectivo',
                'qr'       => 'QR / Billetera',
                'transfer' => 'Transferencia',
                'card'     => 'Tarjeta',
                default    => ucfirst($row->payment_method),
            };
            return $row;
        });
    }

    #[Computed]
    public function comisionesPorBarbero(): Collection
    {
        [$from, $to] = $this->rangoDatetime();

        return SaleItem::whereBetween('created_at', [$from, $to])
            ->selectRaw('staff_id, COUNT(*) as servicios, SUM(commission_amount) as total_comision')
            ->with('staff:id,name')
            ->groupBy('staff_id')
            ->orderByDesc('total_comision')
            ->get();
    }

    #[Computed]
    public function ventasPorBarbero(): Collection
    {
        [$from, $to] = $this->rangoDatetime();

        return SaleItem::whereBetween('created_at', [$from, $to])
            ->selectRaw('staff_id, COUNT(DISTINCT sale_id) as ventas, COUNT(*) as items, SUM(price_at_time * quantity) as total_vendido, SUM(commission_amount) as total_comision')
            ->with('staff:id,name')
            ->groupBy('staff_id')
            ->orderByDesc('total_vendido')
            ->get()
            ->map(function ($row) {
                $row->neto = $row->total_vendido - $row->total_comision;
                return $row;
            });
    }

    #[Computed]
    public function topProductos(): Collection
    {
        [$from, $to] = $this->rangoDatetime();

        return SaleItem::whereBetween('created_at', [$from, $to])
            ->where('item_type', 'product')
            ->selectRaw('item_name, SUM(quantity) as total_qty, SUM(price_at_time * quantity) as total_revenue')
            ->groupBy('item_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();
    }

    // ── Exportar PDF ────────────────────────────────────────────────

    public function exportarPdf(): StreamedResponse
    {
        $resumen           = $this->resumen;
        $ventasPorMetodo   = $this->ventasPorMetodo;
        $comisionesBarbero = $this->comisionesPorBarbero;
        $ventasBarbero     = $this->ventasPorBarbero;
        $topProductos      = $this->topProductos;
        $desde             = $this->desde;
        $hasta             = $this->hasta;

        $html = view('filament.pages.reportes-pdf', compact(
            'resumen', 'ventasPorMetodo', 'comisionesBarbero', 'ventasBarbero', 'topProductos', 'desde', 'hasta'
        ))->render();

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html)->setPaper('a4', 'portrait');

        $filename = 'reporte-' . $desde . '-' . $hasta . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action('exportarPdf'),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin_sucursal']) ?? false;
    }
}
