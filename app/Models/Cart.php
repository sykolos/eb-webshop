<?php

namespace App\Models;

use App\Services\PricingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'last_activity_at', 'session_migration_hash'];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function centstoprice($cents){
        return $cents;
    }
    
    public static function unitprice($item)
    {
        $pricing = app(PricingService::class);
        return $pricing->fromMinorUnits($pricing->toMinorUnits(self::unitnet($item)) * (int) $item['quantity']);
    }

    public static function unitnet($item)
    {
        $product = Products::with('product_unit')->findOrFail($item['product']['id']);

        return app(PricingService::class)->priceForUser($product);
    }

    public static function totalamount(){
        $totalMinor = 0;
        if(session()->has('cart')){
            foreach (session('cart') as $key => $item) {
                $product = Products::findOrFail($item['product']['id']);
                $quote = app(PricingService::class)->quote($product, (int) $item['quantity']);
                $totalMinor += app(PricingService::class)->toMinorUnits($quote['line_net']);
            }

        }
        
        return app(PricingService::class)->fromMinorUnits($totalMinor);
    }
    public static function gettotalvatprice(){
        $total=self::totalamount()+self::gettotalvat();
        return $total;
    }
    public static function gettotalvat(){
        $totalVatMinor = 0;
        if(session()->has('cart')){
            foreach (session('cart') as $key => $item) {
                $product = Products::findOrFail($item['product']['id']);
                $quote = app(PricingService::class)->quote($product, (int) $item['quantity']);
                $totalVatMinor += app(PricingService::class)->toMinorUnits($quote['vat']);
            }

        }
        
        return app(PricingService::class)->fromMinorUnits($totalVatMinor);
    }

}
