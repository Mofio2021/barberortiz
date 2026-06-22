<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerType extends Model
{
    protected $fillable = [
        'name', 'description', 'discount_percentage',
        'cost_bearer', 'color', 'affects_loyalty', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'affects_loyalty'     => 'boolean',
        'is_active'           => 'boolean',
        'discount_percentage' => 'integer',
    ];

    public function customers(): HasMany { return $this->hasMany(Customer::class); }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    public function getCostBearerLabelAttribute(): string
    {
        return match($this->cost_bearer) {
            'business' => 'Negocio asume',
            'barber'   => 'Barbero asume',
            default    => $this->cost_bearer,
        };
    }
}
