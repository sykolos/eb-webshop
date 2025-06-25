<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $guarded=[];



//1 category

    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function items(){
        return $this->belongsTo(Item::class);
    }
    public function product_unit(){
        return $this->hasOne(product_unit::class,'id','unit_id');
    }

    public function special_prices(){
        return $this->hasMany(Special_prices::class,'product_id','id');
    }
    

//sok colors
    // public function colors(){
    //     return $this->belongsToMany(Color::class);
    // }
    	
	public function setGuarded($guarded): self {
		$this->guarded = $guarded;
		return $this;
	}
}