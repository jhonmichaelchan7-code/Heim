<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sizes')->withPivot('price', 'id')->withTimestamps();
    }

    public function productSizes()
    {
        return $this->hasMany(ProductSize::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function categoryRules()
    {
        return $this->hasMany(CategorySizeRule::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
