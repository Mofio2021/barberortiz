<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyReward extends Model
{
    protected $fillable = ['name', 'description', 'points_required', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function redemptions(): HasMany
    {
        return $this->hasMany(LoyaltyRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('points_required')->orderBy('sort_order');
    }
}
