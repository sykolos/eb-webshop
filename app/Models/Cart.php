<?php

namespace App\Models;

use App\Services\PricingService;

class Cart 
{
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
