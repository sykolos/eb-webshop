<?php

namespace App\Models;


class Cart 
{
    public function centstoprice($cents){
        return $cents;
    }
    public static function unitprice($item){
        //price * quantitiy
        $result=0;
        if(!isset($item['s_price'])){
            $result= (int)(new self)->centstoprice((int)$item['product']['price']) * (int)$item['quantity'];
        }
        else{
            $result= (int)(new self)->centstoprice((int)$item['s_price']) * (int)$item['quantity'];
        }
        return $result;
    }

    public static function totalamount(){
        $total=0;
        if(session()->has('cart')){
            foreach (session('cart') as $key => $item) {
                $total+=self::unitprice($item)*$item['q'];
    
            }

        }
        
        return round($total);
    }
    public static function gettotalvatprice(){
        $total=self::totalamount()+self::gettotalvat();
        return $total;
    }
    public static function gettotalvat(){
        $totalvat=0;
        if(session()->has('cart')){
            foreach (session('cart') as $key => $item) {
                $totalvat+=self::unitprice($item)*$item['q']*0.27 ;
                
            }

        }
        
        return round($totalvat);
    }

}
