<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $guarded=[];

    protected $casts = [
        'is_published' => 'boolean',
        'is_archived' => 'boolean',
        'specifications' => 'array',
    ];

    public const AVAILABILITY_STATUSES = [
        'in_stock' => 'Készleten',
        'supplier_stock' => 'Beszállítói készleten',
        'procurement' => 'Beszerzés alatt',
        'out_of_stock' => 'Átmenetileg nem elérhető',
    ];

    public static function availabilityStatuses(): array
    {
        return array_keys(self::AVAILABILITY_STATUSES);
    }

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

    public function media()
    {
        return $this->hasMany(ProductMedia::class, 'product_id')->orderBy('sort_order');
    }

    public function filterValues()
    {
        return $this->belongsToMany(FilterValue::class, 'product_filter_value', 'product_id', 'filter_value_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->where('is_archived', false);
    }
    

	public function setGuarded($guarded): self {
		$this->guarded = $guarded;
		return $this;
	}

   public function getPriceForUser($userId = null)
    {
        return app(\App\Services\PricingService::class)
            ->priceForUser($this, $userId ?? auth()->id());
    }
}
