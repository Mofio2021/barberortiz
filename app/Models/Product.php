<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model {
    protected $fillable = ['category_id','name','description','sku','price','cost_price','stock','stock_min','commission_type','commission_value','is_active'];
    protected $casts = ['is_active'=>'boolean','price'=>'decimal:2'];
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
    public function isLowStock(): bool { return $this->stock <= $this->stock_min && $this->stock > 0; }
    public function isOutOfStock(): bool { return $this->stock <= 0; }
    public function calculateCommission(): float {
        return $this->commission_type === 'percentage'
            ? round(($this->price * $this->commission_value) / 100, 2)
            : (float) $this->commission_value;
    }
}