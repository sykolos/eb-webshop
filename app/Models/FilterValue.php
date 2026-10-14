<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterValue extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function definition()
    {
        return $this->belongsTo(FilterDefinition::class, 'filter_definition_id');
    }

    public function products()
    {
        return $this->belongsToMany(Products::class, 'product_filter_value', 'filter_value_id', 'product_id');
    }
}
