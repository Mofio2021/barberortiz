<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\CommissionPayment;
use App\Models\SaleItem;
use App\Models\Staff;
use App\Models\StaffConsumption;
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
                'consumptionsMonth'   => 0,
                'netMonth'            => 0,
                'lastPayment'         => null,
                'pendingPayments'     => collect(),
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

        $consumptionsMonth = StaffConsumption::where('staff_id', $staff->id)
            ->whereMonth('consumed_at', now()->month)
            ->whereYear('consumed_at', now()->year)
            ->sum('amount');

        $netMonth = max(0, $commissionMonth - $consumptionsMonth);

        $lastPayment = CommissionPayment::where('staff_id', $staff->id)
            ->where('status', 'paid')
            ->orderByDesc('paid_at')
            ->first();

        $pendingPayments = CommissionPayment::where('staff_id', $staff->id)
            ->where('status', 'pending')
            ->orderByDesc('period_end')
            ->get();

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
            'consumptionsMonth',
            'netMonth',
            'lastPayment',
            'pendingPayments',
            'citasHoy',
            'citasManana'
        );
    }
}
