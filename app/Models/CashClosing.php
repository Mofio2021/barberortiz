<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClosing extends Model {
    protected $fillable = ['branch_id','user_id','closing_date','initial_balance','total_sales','cash_sales','qr_sales','transfer_sales','card_sales','total_expenses','total_commissions','net_profit','cash_counted','cash_difference','notes','is_closed','closed_at'];
    protected $casts = ['closing_date'=>'date','is_closed'=>'boolean','closed_at'=>'datetime'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}