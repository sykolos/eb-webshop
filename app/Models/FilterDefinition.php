<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterDefinition extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(FilterValue::class)->orderBy('sort_order')->orderBy('label');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_filter_definition');
    }
}
