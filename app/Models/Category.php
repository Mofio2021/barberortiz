<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // <-- Aquí le especificamos tu tabla exacta del SQL

    protected $fillable = [
        'name',
        'type',
        'color'
    ];

    // Relación con servicios
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // Relación con productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}