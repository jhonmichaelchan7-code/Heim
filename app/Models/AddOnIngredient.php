<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddOnIngredient extends Model
{
    protected $fillable = ['add_on_id', 'ingredient_id', 'quantity', 'unit', 'is_active'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
