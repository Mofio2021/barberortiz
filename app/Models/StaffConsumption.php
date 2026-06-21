<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffConsumption extends Model
{
    protected $fillable = [
        'staff_id', 'branch_id', 'registered_by',
        'description', 'amount', 'consumed_at',
        'commission_payment_id', 'notes',
    ];

    protected $casts = [
        'consumed_at' => 'date',
        'amount'      => 'decimal:2',
    ];

    public function staff(): BelongsTo         { return $this->belongsTo(Staff::class); }
    public function branch(): BelongsTo        { return $this->belongsTo(Branch::class); }
    public function registeredBy(): BelongsTo  { return $this->belongsTo(User::class, 'registered_by'); }
    public function commissionPayment(): BelongsTo { return $this->belongsTo(CommissionPayment::class); }
}
