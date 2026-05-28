<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $ventasHoy      = Sale::whereDate('created_at', today())->sum('total');
        $ventasAyer     = Sale::whereDate('created_at', today()->subDay())->sum('total');
        $cantidadVentas = Sale::whereDate('created_at', today())->count();
        $ventasMes      = Sale::whereMonth('created_at', now()->month)
                               ->whereYear('created_at', now()->year)
                               ->sum('total');

        $citasHoy        = Appointment::whereDate('start_at', today())->count();
        $citasPendientes = Appointment::whereDate('start_at', today())
                                       ->where('status', 'pendiente')
                                       ->count();
        $citasCompletadas = Appointment::whereDate('start_at', today())
                                        ->where('status', 'completada')
                                        ->count();

        $egresosHoy = Expense::whereDate('expense_date', today())->sum('amount');

        $productosBajoStock = Product::where('is_active', true)
                                      ->whereColumn('stock', '<=', 'stock_min')
                                      ->where('stock', '>', 0)
                                      ->count();
        $productosAgotados  = Product::where('is_active', true)
                                      ->where('stock', '<=', 0)
                                      ->count();

        $subeOBaja   = $ventasHoy >= $ventasAyer;
        $iconTendencia = $subeOBaja
            ? 'heroicon-m-arrow-trending-up'
            : 'heroicon-m-arrow-trending-down';

        return [
            Stat::make('Ventas del dia', 'Bs ' . number_format((float) $ventasHoy, 2))
                ->description(
                    $cantidadVentas . ' ventas · ' . ($subeOBaja ? 'Mas' : 'Menos') . ' que ayer'
                )
                ->descriptionIcon($iconTendencia)
                ->color($subeOBaja ? 'success' : 'danger'),

            Stat::make('Citas de hoy', $citasHoy)
                ->description(
                    "{$citasCompletadas} completadas · {$citasPendientes} pendientes"
                )
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($citasPendientes > 0 ? 'warning' : 'success'),

            Stat::make('Ventas del mes', 'Bs ' . number_format((float) $ventasMes, 2))
                ->description('Acumulado ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),

            Stat::make('Egresos del dia', 'Bs ' . number_format((float) $egresosHoy, 2))
                ->description('Gastos registrados hoy')
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
