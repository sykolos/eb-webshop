<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecommendedProduct extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Products::class,'product_id');
    }
}
