<?php

namespace App\Http\Controllers;

use App\Models\Special_prices;
use App\Models\Products;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addtocart(Request $request, $id, $q, $m)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Products::with(['special_prices', 'product_unit'])->findOrFail($id);
        $quantity = (int) $validated['quantity'];
        $unit = $product->product_unit;

        // Keep the legacy route shape, but source packaging from the product record.
        $item = [
            'product' => $product->toArray(),
            'quantity' => $quantity,
            'q' => (int) ($unit?->quantity ?: 1),
            'm' => $unit?->measure ?: 'db',
        ];

        if (session()->has('cart')) {
            $cart = session()->get('cart');
            $key = $this->checkItemInCart($item);

            if ($key != -1) {
                $cart[$key]['quantity'] += $quantity;
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
        $validated = $request->validate([
            'key' => ['required', 'integer', 'min:0'],
            'change' => ['required', 'integer', 'in:-1,1'],
        ]);

        $key = (int) $validated['key'];
        $change = (int) $validated['change'];

        $cart = session('cart', []);
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = max(1, $cart[$key]['quantity'] + $change);
            session()->put('cart', $cart);
        }

        return response()->noContent();
    }

    public function removeItem(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'integer', 'min:0'],
        ]);

        $key = (int) $validated['key'];
        $cart = session('cart', []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', array_values($cart)); // újraindexelés
        }

        return response()->noContent();
    }

}
