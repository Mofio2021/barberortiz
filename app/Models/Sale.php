<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Sale extends Model {
    protected $fillable = ['branch_id','customer_id','staff_id','cashier_id','subtotal','discount','total','total_commission','payment_method','amount_paid','change_given','notes','qr_receipt_path'];
    protected $casts = ['total'=>'decimal:2','subtotal'=>'decimal:2'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function cashier(): BelongsTo { return $this->belongsTo(User::class, 'cashier_id'); }
    public function items(): HasMany { return $this->hasMany(SaleItem::class); }
    public function getPaymentLabelAttribute(): string {
        return match($this->payment_method) {
            'cash'=>'Efectivo','qr'=>'QR','transfer'=>'Transferencia','card'=>'Tarjeta',default=>$this->payment_method
        };
    }
}
