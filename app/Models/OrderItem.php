<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'size_id',
        'product_name', 'size_name', 'unit_price', 'quantity', 'subtotal',
        'discount_type', 'discount_rate', 'discount', 'is_vat_exempt', 'tax', 'total', 'id_number',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'is_vat_exempt' => 'boolean',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }

    public function addOns()
    {
        return $this->hasMany(OrderItemAddOn::class);
    }

    public function getTotalWithAddOnsAttribute(): float
    {
        return $this->subtotal + $this->addOns->sum('add_on_price');
    }
}
