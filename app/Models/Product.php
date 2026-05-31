<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'branch_id',
        'category_id',
        'name',
        'description',
        'sku',
        'price',
        'cost_price',
        'stock',
        'stock_min',
        'commission_type',
        'commission_value',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    // Retorna productos globales + los de una sucursal específica.
    // branch_id = null → global (stock compartido / visible en todas las sucursales).
    public function scopeForBranch(Builder $q, ?int $branchId): Builder
    {
        if (! $branchId) {
            return $q;
        }

        return $q->where(function (Builder $inner) use ($branchId) {
            $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
        });
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->stock_min && $this->stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function calculateCommission(): float
    {
        return $this->commission_type === 'percentage'
            ? round(($this->price * $this->commission_value) / 100, 2)
            : (float) $this->commission_value;
    }
}
