<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'branch_id',
        'category_id',
        'name',
        'description',
        'price',
        'duration_minutes',
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

    // Retorna servicios globales + los de una sucursal específica.
    // branch_id = null → global (visible en todas las sucursales).
    public function scopeForBranch(Builder $q, ?int $branchId): Builder
    {
        if (! $branchId) {
            return $q;
        }

        return $q->where(function (Builder $inner) use ($branchId) {
            $inner->whereNull('branch_id')->orWhere('branch_id', $branchId);
        });
    }
}
