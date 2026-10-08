<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemAddOnIngredient extends Model
{
    protected $fillable = ['order_item_add_on_id', 'ingredient_id', 'quantity'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function orderItemAddOn()
    {
        return $this->belongsTo(OrderItemAddOn::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
