<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CajeroStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    // Solo para cajero — barbero tiene BarberoStatsWidget, admin tiene DailyStatsWidget
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasRole('cajero');
    }

    protected function getStats(): array
    {
        $ventasHoy      = (float) Sale::whereDate('created_at', today())->sum('total');
        $cashHoy        = (float) Sale::whereDate('created_at', today())->where('payment_method', 'cash')->sum('total');
        $qrHoy          = (float) Sale::whereDate('created_at', today())->where('payment_method', 'qr')->sum('total');
        $cantidadVentas = (int)   Sale::whereDate('created_at', today())->count();

        $citasHoy        = (int) Appointment::whereDate('start_at', today())->count();
        $citasCompletadas = (int) Appointment::whereDate('start_at', today())->where('status', 'completada')->count();

        $egresosHoy = (float) Expense::whereDate('expense_date', today())->sum('amount');

        return [
            Stat::make('Ventas de hoy', 'Bs ' . number_format($ventasHoy, 2))
                ->description("{$cantidadVentas} transacciones · " . today()->isoFormat('D [de] MMMM'))
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('success'),

            Stat::make('Efectivo cobrado', 'Bs ' . number_format($cashHoy, 2))
                ->description('Solo pagos en efectivo de hoy')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),

            Stat::make('QR cobrado', 'Bs ' . number_format($qrHoy, 2))
                ->description('Pagos QR verificados hoy')
                ->descriptionIcon('heroicon-o-qr-code')
                ->color('info'),

            Stat::make('Citas de hoy', $citasHoy)
                ->description("{$citasCompletadas} completadas")
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color($citasHoy > 0 ? 'warning' : 'gray'),

            Stat::make('Egresos de hoy', 'Bs ' . number_format($egresosHoy, 2))
                ->description('Gastos registrados hoy')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color($egresosHoy > 0 ? 'danger' : 'gray'),
        ];
    }
}
