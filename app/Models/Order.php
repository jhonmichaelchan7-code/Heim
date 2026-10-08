<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'order_type', 'grab_order_code', 'rider_code', 'cashier_name', 'user_id', 'shift_id', 'branch_id',
        'subtotal', 'discount', 'tax_rate', 'tax', 'vatable_sales', 'vat_exempt_sales', 'total', 'status', 'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'vatable_sales' => 'decimal:2',
        'vat_exempt_sales' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function getPaidAmountAttribute(): float
    {
        $payments = $this->payments()->get();
        if ($payments->isEmpty()) {
            return $this->payment ? (float) $this->payment->amount_tendered - (float) $this->payment->change : 0.0;
        }
        return (float) $payments->sum('amount_tendered') - (float) $payments->sum('change');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0, round((float) $this->total - $this->paid_amount, 2));
    }

    public function getOrderTypeLabelAttribute(): string
    {
        return match ($this->order_type) {
            'takeout' => 'Takeout',
            'grab_delivery' => 'Grab Delivery',
            default => 'Dine-In',
        };
    }

    public function getOrderTypeIconAttribute(): string
    {
        return match ($this->order_type) {
            'takeout' => '🛍️',
            'grab_delivery' => '🛵',
            default => '🍽️',
        };
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByBranch($query, $branchId)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    public function scopeByOrderType($query, $type)
    {
        if ($type && $type !== 'all') {
            return $query->where('order_type', $type);
        }
        return $query;
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public static function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        return "ORD-{$today}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
