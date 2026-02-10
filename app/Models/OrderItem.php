<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'pizza_id',
        'pizza_name',
        'quantity',
        'size',
        'crust',
        'is_custom',
        'unit_price',
        'toppings_price',
        'total_price',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'is_custom' => 'boolean',
            'unit_price' => 'decimal:2',
            'toppings_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    /**
     * Get the order that owns this item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the pizza for this order item.
     */
    public function pizza()
    {
        return $this->belongsTo(Pizza::class);
    }

    /**
     * Get the toppings for this order item.
     */
    public function toppings()
    {
        return $this->belongsToMany(Topping::class, 'order_item_toppings')
            ->withPivot('topping_name', 'topping_price')
            ->withTimestamps();
    }
}
