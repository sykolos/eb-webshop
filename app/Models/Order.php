<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function user(){
        return $this->belongsTo(User::class,);
    }
    public function items(){
        return $this->hasMany(Item::class);
    }
    public function user_shipping()
    {
        return $this->belongsTo(User_shipping::class, 'shipping_address_id');
    }
    public function user_invoice()
    {
        return $this->hasOne(User_invoce::class, 'user_id', 'user_id');
    }
}
