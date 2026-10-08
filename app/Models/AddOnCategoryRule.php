<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddOnCategoryRule extends Model
{
    protected $fillable = ['add_on_id', 'category_id', 'only_iced_sizes', 'is_active'];

    protected $casts = [
        'only_iced_sizes' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
