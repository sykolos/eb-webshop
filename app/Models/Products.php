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
        $basePrice = $this->price;

        // 1. Get the Category Discount for this user
        // Assumes a 'category_discount' relationship exists on the Product model
        $categoryDiscount = \DB::table('group_discounts')
            ->where('user_id', $userId)
            ->where('category_id', $this->category_id)
            ->first();

        $catDiscPercent = $categoryDiscount?->discount_percent ?? 0;

        // 2. Get the Special Price/Extra Percent for this user
        $special = $this->special_prices->firstWhere('user_id', $userId);

        // 3. Priority 1: If there is a Fix Price, return it immediately
        if ($special && $special->price) {
            return $special->price;
        }

        // 4. Priority 2: Calculate price based on Cat % + Extra %
        $extraDiscPercent = $special?->extra_percent ?? 0;
        $totalDiscount = $catDiscPercent + $extraDiscPercent;

        // Apply discount and round (matching your JS logic)
        if ($totalDiscount > 0) {
            return round($basePrice * (1 - ($totalDiscount / 100)));
        }

        return $basePrice;
    }
}