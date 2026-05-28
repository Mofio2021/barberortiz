<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model {
    protected $fillable = ['sale_id','staff_id','item_type','item_id','item_name','price_at_time','quantity','commission_amount'];
    protected $casts = ['price_at_time'=>'decimal:2','commission_amount'=>'decimal:2'];
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function getSubtotalAttribute(): float { return $this->price_at_time * $this->quantity; }
}
