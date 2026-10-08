<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    use HasFactory;

    protected $table = 'add_ons';

    protected $fillable = ['name', 'price', 'ingredient_id', 'quantity', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'add_on_ingredients')
            ->withPivot('quantity', 'unit')
            ->withTimestamps();
    }

    public function categoryRules()
    {
        return $this->hasMany(AddOnCategoryRule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
