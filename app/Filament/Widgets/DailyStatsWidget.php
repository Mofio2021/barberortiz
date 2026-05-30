<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DailyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // Solo visible para dueño y administradores de sucursal
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    // Período seleccionado: today | week | month
    public string $period = 'today';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('today')
                ->label('Hoy')
                ->size('sm')
                ->color($this->period === 'today' ? 'primary' : 'gray')
                ->action(fn () => $this->period = 'today'),

            Action::make('week')
                ->label('Semana')
                ->size('sm')
                ->color($this->period === 'week' ? 'primary' : 'gray')
                ->action(fn () => $this->period = 'week'),

            Action::make('month')
                ->label('Mes')
                ->size('sm')
                ->color($this->period === 'month' ? 'primary' : 'gray')
                ->action(fn () => $this->period = 'month'),
        ];
    }

    protected function getStats(): array
    {
        // Rango del período seleccionado
        [$from, $to, $periodLabel] = match ($this->period) {
            'week'  => [
                now()->startOfWeek(),
                now()->endOfWeek(),
                'Semana del ' . now()->startOfWeek()->isoFormat('D MMM'),
            ],
            'month' => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->isoFormat('MMMM YYYY'),
            ],
            default => [
                today()->startOfDay(),
                today()->endOfDay(),
                today()->isoFormat('dddd D [de] MMMM'),
            ],
        };

        // Rango del período anterior para comparación
        [$prevFrom, $prevTo] = match ($this->period) {
            'week'  => [now()->subWeek()->startOfWeek(),  now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default => [today()->subDay()->startOfDay(),  today()->subDay()->endOfDay()],
        };

        $ventas         = (float) Sale::whereBetween('created_at', [$from, $to])->sum('total');
        $ventasAnteriores = (float) Sale::whereBetween('created_at', [$prevFrom, $prevTo])->sum('total');
        $cantidadVentas = (int)   Sale::whereBetween('created_at', [$from, $to])->count();

        $citasHoy         = (int) Appointment::whereDate('start_at', today())->count();
        $citasPendientes  = (int) Appointment::whereDate('start_at', today())
                                              ->where('status', 'pendiente')->count();
        $citasCompletadas = (int) Appointment::whereDate('start_at', today())
                                              ->where('status', 'completada')->count();

        $egresos = (float) Expense::whereBetween('expense_date', [
            $from->toDateString(),
            $to->toDateString(),
        ])->sum('amount');

        $productosBajoStock = (int) Product::where('is_active', true)
                                            ->whereColumn('stock', '<=', 'stock_min')
                                            ->where('stock', '>', 0)
                                            ->count();
        $productosAgotados  = (int) Product::where('is_active', true)
                                            ->where('stock', '<=', 0)
                                            ->count();

        $sube          = $ventas >= $ventasAnteriores;
        $iconTendencia = $sube ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $periodoAnteriorLabel = match ($this->period) {
            'week'  => 'semana pasada',
            'month' => 'mes pasado',
            default => 'ayer',
        };

        return [
            Stat::make('Ventas · ' . $periodLabel, 'Bs ' . number_format($ventas, 2))
                ->description(
                    $cantidadVentas . ' ventas · ' . ($sube ? 'Más' : 'Menos') . ' que ' . $periodoAnteriorLabel
                )
                ->descriptionIcon($iconTendencia)
                ->color($sube ? 'success' : 'danger'),

            Stat::make('Citas de hoy', $citasHoy)
                ->description("{$citasCompletadas} completadas · {$citasPendientes} pendientes")
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($citasPendientes > 0 ? 'warning' : 'success'),

            Stat::make('Período anterior', 'Bs ' . number_format($ventasAnteriores, 2))
                ->description(ucfirst($periodoAnteriorLabel))
                ->descriptionIcon('heroicon-o-clock')
                ->color('gray'),

            Stat::make('Egresos · ' . $periodLabel, 'Bs ' . number_format($egresos, 2))
                ->description('Gastos registrados')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make('Inventario', match (true) {
                $productosAgotados > 0  => "{$productosAgotados} agotados",
                $productosBajoStock > 0 => "{$productosBajoStock} stock bajo",
                default                 => 'Todo en stock',
            })
                ->descriptionIcon('heroicon-o-archive-box')
                ->color(match (true) {
                    $productosAgotados > 0  => 'danger',
                    $productosBajoStock > 0 => 'warning',
                    default                 => 'success',
                }),
        ];
    }
}
