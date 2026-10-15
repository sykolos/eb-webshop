<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $part) => $part->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $part) => $part->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderByDesc('id');
    }

    public function products()
    {
        return $this->belongsToMany(Products::class, 'offer_product', 'offer_id', 'product_id')
            ->withPivot('sort_order')->orderBy('offer_product.sort_order')->orderBy('products.title');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'offer_category', 'offer_id', 'category_id')
            ->withPivot('sort_order')->orderBy('offer_category.sort_order')->orderBy('categories.name');
    }

    public function banners()
    {
        return $this->hasMany(Banner::class, 'offer_id');
    }
}
