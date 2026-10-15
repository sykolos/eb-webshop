<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAvailabilityHistory extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['created_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
