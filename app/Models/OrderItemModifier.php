<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 'modifier_option_id', 'group_name', 'option_name',
        'price', 'ingredient_id', 'consumed_quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'consumed_quantity' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function option()
    {
        return $this->belongsTo(ModifierOption::class, 'modifier_option_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
