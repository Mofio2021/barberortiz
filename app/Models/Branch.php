<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model {
    protected $fillable = ['name','address','phone','city','is_main','is_active','open_time','close_time'];
    protected $casts = ['is_active'=>'boolean','is_main'=>'boolean'];
    public function staff(): HasMany { return $this->hasMany(Staff::class); }
    public function sales(): HasMany { return $this->hasMany(Sale::class); }
    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
}
