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
    public function cartCount()
    {
        $count = collect(session('cart', []))->sum('quantity');
        return response()->json(['count' => $count]);
    }
    public function previewcart()
    {
        $cart = session('cart', []);
        $totalNet = 0;

        foreach ($cart as $item) {
            $netUnitPrice = floatval($item['product']['price'] ?? 0);
            $totalNet += $netUnitPrice * (int) $item['quantity'];
        }


        return view('pages.components.cart.cart-preview', [
            'cart' => $cart,
            'totalNet' => $totalNet,
        ]);  
    }  
    public function updateQuantity(Request $request)
    {
        $key = $request->input('key');
        $change = (int) $request->input('change'); // +1 vagy -1

        $cart = session('cart', []);
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = max(1, $cart[$key]['quantity'] + $change);
            session()->put('cart', $cart);
        }

        return response()->noContent();
    }

    public function removeItem(Request $request)
    {
        $key = $request->input('key');
        $cart = session('cart', []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', array_values($cart)); // újraindexelés
        }

        return response()->noContent();
    }

}
