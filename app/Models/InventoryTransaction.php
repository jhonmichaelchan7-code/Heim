<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingredient_id', 'type', 'quantity', 'previous_stock', 'new_stock',
        'reference_type', 'reference_id', 'supplier', 'reason', 'notes', 'performed_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'previous_stock' => 'decimal:2',
        'new_stock' => 'decimal:2',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'stock_in' => 'Stock In',
            'sales_consumption' => 'Sales Consumption',
            'waste' => 'Waste/Spoilage',
            'adjustment' => 'Adjustment',
            default => ucfirst($this->type),
        };
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'stock_in' => 'green',
            'sales_consumption' => 'blue',
            'waste' => 'red',
            'adjustment' => 'yellow',
            default => 'gray',
        };
    }
}
