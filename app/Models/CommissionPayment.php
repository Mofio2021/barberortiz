<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionPayment extends Model
{
    protected $fillable = [
        'staff_id', 'branch_id',
        'period_start', 'period_end',
        'gross_amount', 'deductions', 'net_amount',
        'status', 'paid_at', 'paid_by', 'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'paid_at'      => 'datetime',
        'gross_amount' => 'decimal:2',
        'deductions'   => 'decimal:2',
        'net_amount'   => 'decimal:2',
    ];

    public function staff(): BelongsTo        { return $this->belongsTo(Staff::class); }
    public function branch(): BelongsTo       { return $this->belongsTo(Branch::class); }
    public function paidBy(): BelongsTo       { return $this->belongsTo(User::class, 'paid_by'); }
    public function consumptions(): HasMany   { return $this->hasMany(StaffConsumption::class); }

    public function scopePending($q)          { return $q->where('status', 'pending'); }
    public function scopePaid($q)             { return $q->where('status', 'paid'); }

    public function isPending(): bool         { return $this->status === 'pending'; }
    public function isPaid(): bool            { return $this->status === 'paid'; }

    public function recalculate(): void
    {
        $gross = SaleItem::where('staff_id', $this->staff_id)
            ->whereBetween('created_at', [
                $this->period_start->startOfDay(),
                $this->period_end->endOfDay(),
            ])
            ->sum('commission_amount');

        $deductions = StaffConsumption::where('staff_id', $this->staff_id)
            ->whereBetween('consumed_at', [$this->period_start, $this->period_end])
            ->sum('amount');

        $this->update([
            'gross_amount' => $gross,
            'deductions'   => $deductions,
            'net_amount'   => max(0, $gross - $deductions),
        ]);
    }
}
