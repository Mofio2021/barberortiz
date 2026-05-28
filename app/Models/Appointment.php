<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model {
    protected $fillable = ['branch_id','customer_id','staff_id','service_id','start_at','end_at','status','notes','sale_id'];
    protected $casts = ['start_at'=>'datetime','end_at'=>'datetime'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function staff(): BelongsTo { return $this->belongsTo(Staff::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
}