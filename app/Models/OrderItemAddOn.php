<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemAddOn extends Model
{
    use HasFactory;

    protected $table = 'order_item_add_ons';

    protected $fillable = ['order_item_id', 'add_on_id', 'add_on_name', 'add_on_price'];

    protected $casts = [
        'add_on_price' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }
}
