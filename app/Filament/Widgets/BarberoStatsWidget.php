<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\SaleItem;
use App\Models\Staff;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class BarberoStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.barbero-stats-widget';

    protected static ?int $sort = 1;

    // Refresca automáticamente cada 60 segundos
    protected static ?string $pollingInterval = '60s';

    // Ocupa todo el ancho del dashboard
    protected int | string | array $columnSpan = 'full';

    // Solo visible para barberos — admins y cajeros tienen DailyStatsWidget
    public static function canView(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasRole('barbero');
    }

    protected function getViewData(): array
    {
        $user  = Auth::user();
        $staff = $user instanceof User
            ? Staff::where('user_id', $user->id)->first()
            : null;

        if (! $staff) {
            return [
                'staff'               => null,
                'commissionToday'     => 0,
                'commissionYesterday' => 0,
                'commissionMonth'     => 0,
                'citasHoy'            => collect(),
                'citasManana'         => collect(),
            ];
        }

        $commissionToday = SaleItem::where('staff_id', $staff->id)
            ->whereDate('created_at', today())
            ->sum('commission_amount');

        $commissionYesterday = SaleItem::where('staff_id', $staff->id)
            ->whereDate('created_at', today()->subDay())
            ->sum('commission_amount');

        $commissionMonth = SaleItem::where('staff_id', $staff->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('commission_amount');

        $citasHoy = Appointment::with(['customer', 'service'])
            ->where('staff_id', $staff->id)
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        $citasManana = Appointment::with(['customer', 'service'])
            ->where('staff_id', $staff->id)
            ->whereDate('start_at', today()->addDay())
            ->orderBy('start_at')
            ->get();

        return compact(
            'staff',
            'commissionToday',
            'commissionYesterday',
            'commissionMonth',
            'citasHoy',
            'citasManana'
        );
    }
}
