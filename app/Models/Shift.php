<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'opened_by',
        'opened_at',
        'closed_by',
        'closed_at',
        'starting_cash',
        'cash_in',
        'cash_out',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'discrepancy_reason',
        'authorized_by',
        'branch_id',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'starting_cash' => 'decimal:2',
        'cash_in' => 'decimal:2',
        'cash_out' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(ShiftCashMovement::class);
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Calculate live metrics for the shift
     */
    public function calculateMetrics(): array
    {
        $orders = $this->orders()->with('payment')->get();
        $movements = $this->cashMovements()->get();

        $grossSales = $orders->where('status', 'completed')->sum('total');
        $discounts = $orders->where('status', 'completed')->sum('discount');
        $netSales = $grossSales;

        // Cash In from POS transactions
        $cashOrderSales = 0;
        $onlineOrderSales = 0;

        foreach ($orders->where('status', 'completed') as $order) {
            $method = strtolower($order->payment?->method ?? 'cash');
            if ($method === 'cash') {
                $cashOrderSales += $order->total;
            } else {
                $onlineOrderSales += $order->total;
            }
        }

        // Manual Cash Movements
        $manualCashIn = $movements->where('type', 'cash_in')->sum('amount');
        $manualCashOut = $movements->where('type', 'cash_out')->sum('amount');

        $totalCashIn = $cashOrderSales + $manualCashIn;
        $totalCashOut = $manualCashOut;
        $expectedCash = $this->starting_cash + $totalCashIn - $totalCashOut;

        $completedTransactions = $orders->where('status', 'completed')->count();
        $voidTransactions = $orders->whereIn('status', ['cancelled', 'refunded'])->count();

        return [
            'starting_cash' => (float) $this->starting_cash,
            'cash_in' => (float) $totalCashIn,
            'cash_out' => (float) $totalCashOut,
            'expected_cash' => (float) $expectedCash,
            'pos_cash_sales' => (float) $cashOrderSales,
            'online_sales' => (float) $onlineOrderSales,
            'manual_cash_in' => (float) $manualCashIn,
            'manual_cash_out' => (float) $manualCashOut,
            'gross_sales' => (float) $grossSales,
            'discounts' => (float) $discounts,
            'net_sales' => (float) $netSales,
            'transactions_count' => $completedTransactions,
            'voids_count' => $voidTransactions,
        ];
    }
}
