<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyRedemption extends Model
{
    protected $fillable = [
        'customer_id', 'loyalty_reward_id', 'points_spent',
        'redeemed_by', 'sale_id', 'notes', 'redeemed_at',
    ];

    protected $casts = ['redeemed_at' => 'datetime'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function reward(): BelongsTo  { return $this->belongsTo(LoyaltyReward::class, 'loyalty_reward_id'); }
    public function redeemedBy(): BelongsTo { return $this->belongsTo(User::class, 'redeemed_by'); }
    public function sale(): BelongsTo   { return $this->belongsTo(Sale::class); }
}
