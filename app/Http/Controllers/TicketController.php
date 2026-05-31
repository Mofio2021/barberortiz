<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Sale;

class TicketController extends Controller
{
    public function venta(Sale $sale)
    {
        $sale->load(['customer', 'staff', 'items', 'cashier']);

        return view('filament.tickets.ticket-venta', compact('sale'));
    }

    public function cierre(CashRegister $cashRegister)
    {
        $cashRegister->load(['branch', 'user']);

        return view('filament.tickets.ticket-cierre', compact('cashRegister'));
    }
}
