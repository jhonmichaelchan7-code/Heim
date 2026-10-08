<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategorySizeRule extends Model
{
    protected $fillable = ['category_id', 'size_id', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function size()
    {
        return $this->belongsTo(Size::class);
    }
}
