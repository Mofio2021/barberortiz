<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Staff extends Model {
    protected $fillable = ['branch_id','user_id','name','role','phone','avatar','status','payment_type','commission_type','commission_value','base_salary'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function saleItems(): HasMany { return $this->hasMany(SaleItem::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }

    public function calculateCommission(float $price): float {
        return $this->commission_type === 'percentage'
            ? round(($price * $this->commission_value) / 100, 2)
            : (float) $this->commission_value;
    }

    public function commissionsForPeriod(string $from, string $to): float {
        return $this->saleItems()
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->sum('commission_amount');
    }
}