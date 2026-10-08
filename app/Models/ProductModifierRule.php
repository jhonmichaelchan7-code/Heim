<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductModifierRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'modifier_group_id', 'min_choices', 'max_choices', 'wings_per_order', 'dips_included',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function group()
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }
}
