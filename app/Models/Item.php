<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function product(){
        return $this->belongsTo(Products::class, 'product_id');
    }
    public function order(){
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function getUnitPriceAttribute($value)
{
    if (!is_null($value)) {
        return $value;
    }

    // Fallback: ha nincs elmentve az ár, visszaadjuk a jelenlegi árlogika szerint
    return $this->product->getPriceForUser(optional($this->order)->user_id ?? auth()->id());
}
}
