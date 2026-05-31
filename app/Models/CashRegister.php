<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'status',
        'opening_balance', 'closing_balance',
        'total_cash_sales', 'total_qr_sales', 'total_expenses',
        'notes', 'opened_at', 'closed_at',
    ];

    protected $casts = [
        'opened_at'  => 'datetime',
        'closed_at'  => 'datetime',
        'opening_balance'  => 'decimal:2',
        'closing_balance'  => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_qr_sales'   => 'decimal:2',
        'total_expenses'   => 'decimal:2',
    ];

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }

    // Saldo teórico de efectivo al cierre = inicial + ventas efectivo − egresos
    public function getExpectedCashAttribute(): float
    {
        return (float) $this->opening_balance
             + (float) $this->total_cash_sales
             - (float) $this->total_expenses;
    }
}
