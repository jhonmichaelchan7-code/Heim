<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifierOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'modifier_group_id', 'name', 'price', 'ingredient_id', 'grams_per_wing', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'grams_per_wing' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(ModifierGroup::class, 'modifier_group_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
