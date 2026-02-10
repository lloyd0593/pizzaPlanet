<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'session_id',
        'user_id',
        'pizza_id',
        'quantity',
        'size',
        'crust',
        'is_custom',
        'custom_name',
        'unit_price',
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
        ];
    }

    /**
     * Get the pizza for this cart item.
     */
    public function pizza()
    {
        return $this->belongsTo(Pizza::class);
    }

    /**
     * Get the user for this cart item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the toppings for this cart item.
     */
    public function toppings()
    {
        return $this->belongsToMany(Topping::class, 'cart_item_toppings')
            ->withTimestamps();
    }

    /**
     * Calculate the total price for this cart item including toppings.
     */
    public function getTotalPriceAttribute(): float
    {
        $toppingsTotal = $this->toppings->sum('price');
        return ($this->unit_price + $toppingsTotal) * $this->quantity;
    }
}
