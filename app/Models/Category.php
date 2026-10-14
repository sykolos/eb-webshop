<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $guarded=[];

    public function products(){
        return $this->hasMany(Products::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function filterDefinitions()
    {
        return $this->belongsToMany(FilterDefinition::class, 'category_filter_definition');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
