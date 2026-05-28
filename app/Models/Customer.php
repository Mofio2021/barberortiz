<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Customer extends Model {
    protected $fillable = ['branch_id','name','phone','email','birth_date','notes','loyalty_points','total_visits','last_visit'];
    protected $casts = ['birth_date'=>'date','last_visit'=>'date'];
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
    public function isBirthday(): bool {
        return $this->birth_date && $this->birth_date->format('m-d') === now()->format('m-d');
    }
}