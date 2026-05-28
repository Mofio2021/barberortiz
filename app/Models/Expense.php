<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model {
    protected $fillable = ['branch_id','user_id','category','description','amount','payment_method','expense_date','notes'];
    protected $casts = ['expense_date'=>'date','amount'=>'decimal:2'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
