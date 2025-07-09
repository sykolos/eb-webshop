<?php

namespace App\Http\Controllers;

use App\Models\Special_prices;
use App\Models\Products;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addtocart(Request $request, $id, $q, $m)
    {
        $product = Products::with('special_prices')->findOrFail($id); // árak betöltése itt

        $item = [
            'product' => $product->toArray(),
            'quantity' => $request->quantity,
            'q' => $q,
            'm' => $m,
        ];

        if (session()->has('cart')) {
            $cart = session()->get('cart');
            $key = $this->checkItemInCart($item);

            if ($key != -1) {
                $cart[$key]['quantity'] += $request->quantity;
                session()->put('cart', $cart);
            } else {
                session()->push('cart', $item);
            }
        } else {
            session()->push('cart', $item);
        }

        return back()->with('addedtocart', 'Hozzáadva a kosárhoz');
    }

    public function checkItemInCart($item){

        foreach(session()->get('cart') as $key =>$val)
        {
            if($val['product']['id'] == $item['product']['id']){
                return $key;
            }
        }
        return -1;

    }
    public function removefromcart($key){
        if(session()->has('cart')){
            $cart=session()->get('cart');
            array_splice($cart,$key,1);
            session()->put('cart',$cart);
            return back()->with('success','Termék törölve.');
        }
        return back();
    }
        

    
}
