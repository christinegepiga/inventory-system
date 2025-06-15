<?php

// app/Models/Product.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'description', 'price', 'initial_quantity'];

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Accessor for current quantity
    public function getCurrentQuantityAttribute()
    {
        return $this->initial_quantity + $this->movements->sum(function($movement) {
            return $movement->type === 'in' ? $movement->quantity : -$movement->quantity;
        });
    }
}