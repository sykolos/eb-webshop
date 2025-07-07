<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User_shipping extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $table = 'user_shippings';
    protected $fillable = [
        'user_id',
        'address',
        'city',
        'zipcode',
        'phone',
        'receiver',
        'comment',
    ];
    
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    public function setGuarded($guarded): self {
		$this->guarded = $guarded;
		return $this;
	}
}
