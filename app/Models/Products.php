<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $guarded=[];

    protected $with = ['special_prices'];

    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function items(){
        return $this->belongsTo(Item::class);
    }
    public function product_unit(){
        return $this->belongsTo(product_unit::class,'unit_id');
    }

    public function special_prices(){
        return $this->hasMany(Special_prices::class,'product_id','id');
    }
    

	public function setGuarded($guarded): self {
		$this->guarded = $guarded;
		return $this;
	}

    public function getPriceForUser($userId = null)
    {
        $userId = $userId ?? auth()->id();
        $special = $this->special_prices->firstWhere('user_id', $userId);
        return $special?->price ?? $this->price;
    }
}