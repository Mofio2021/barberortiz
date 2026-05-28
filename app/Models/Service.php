<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model {
    protected $fillable = ['category_id','name','description','price','duration_minutes','commission_type','commission_value','is_active'];
    protected $casts = ['is_active'=>'boolean','price'=>'decimal:2'];
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
