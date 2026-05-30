<?php

namespace App\Filament\Resources\CashClosingResource\Pages;

use App\Filament\Resources\CashClosingResource;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCashClosing extends CreateRecord
{
    protected static string $resource = CashClosingResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Auto-rellena los totales financieros desde las ventas y egresos del día
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $date     = $data['closing_date'] ?? today()->toDateString();
        $branchId = $data['branch_id']    ?? Auth::user()?->branch_id;

        if (! $branchId) {
            return $data;
        }

        // Consulta base de ventas del día en la sucursal
        $baseQuery = Sale::whereDate('created_at', $date)
            ->where('branch_id', $branchId);

        $cashSales     = (float) (clone $baseQuery)->where('payment_method', 'cash')->sum('total');
        $qrSales       = (float) (clone $baseQuery)->where('payment_method', 'qr')->sum('total');
        $transferSales = (float) (clone $baseQuery)->where('payment_method', 'transfer')->sum('total');
        $cardSales     = (float) (clone $baseQuery)->where('payment_method', 'card')->sum('total');
        $totalSales    = $cashSales + $qrSales + $transferSales + $cardSales;

        $totalExpenses = (float) Expense::whereDate('expense_date', $date)
            ->where('branch_id', $branchId)
            ->sum('amount');

        $totalCommissions = (float) SaleItem::whereHas('sale', fn ($q) =>
            $q->whereDate('created_at', $date)->where('branch_id', $branchId)
        )->sum('commission_amount');

        $netProfit = $totalSales - $totalExpenses - $totalCommissions;

        $data['user_id']           = Auth::id();
        $data['branch_id']         = $branchId;
        $data['total_sales']       = $totalSales;
        $data['cash_sales']        = $cashSales;
        $data['qr_sales']          = $qrSales;
        $data['transfer_sales']    = $transferSales;
        $data['card_sales']        = $cardSales;
        $data['total_expenses']    = $totalExpenses;
        $data['total_commissions'] = $totalCommissions;
        $data['net_profit']        = $netProfit;

        // Calcular diferencia si ya se ingresó efectivo contado
        if (isset($data['cash_counted']) && filled($data['cash_counted'])) {
            $balanceReal      = (float) ($data['initial_balance'] ?? 0) + $cashSales - $totalExpenses;
            $data['cash_difference'] = (float) $data['cash_counted'] - $balanceReal;
        }

        return $data;
    }
}
