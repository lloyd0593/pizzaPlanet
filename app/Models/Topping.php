<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topping extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'price',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the pizzas that have this topping.
     */
    public function pizzas()
    {
        return $this->belongsToMany(Pizza::class, 'pizza_toppings')
            ->withTimestamps();
    }

    /**
     * Scope to only active toppings.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
