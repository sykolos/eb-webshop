<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use App\Services\CartService;
use App\Services\OrderNotificationService;

class Checkout extends Controller
{
    
    public function checkout(CheckoutRequest $request, CheckoutService $checkoutService, CartService $carts, OrderNotificationService $notifications)
    {
        $validated = $request->validated();

        $order = $checkoutService->createOrder(
            auth()->user(),
            $validated + ['is_direct_shipping' => $request->boolean('is_direct_shipping')],
            $carts->forUser($request->user())
        );
        $isNewOrder = $order->wasRecentlyCreated;

        if ($isNewOrder) {
            $carts->clear($request->user());
            $carts->markConverted($request->user());
        }
        session()->forget(['cart', 'checkout_token']);

        if ($isNewOrder) {
            $notifications->dispatch($order);
        }

        return view('pages.orderSuccess', ['order' => $order]);
    }

    
}
