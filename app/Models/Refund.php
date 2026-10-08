<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'refund_amount', 'reason', 'restored_inventory', 'action_type', 'cashier_name', 'authorized_by'];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'restored_inventory' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function authorizer()
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }
}
