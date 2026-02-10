<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pizza extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'image_url',
        'is_active',
        'size',
        'crust',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the toppings for the pizza (predefined toppings).
     */
    public function toppings()
    {
        return $this->belongsToMany(Topping::class, 'pizza_toppings')
            ->withTimestamps();
    }

    /**
     * Scope to only active pizzas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
