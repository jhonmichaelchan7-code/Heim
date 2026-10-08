<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'is_active'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function productRules()
    {
        return $this->hasMany(ProductModifierRule::class);
    }
}
