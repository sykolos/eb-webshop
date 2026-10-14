<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use App\Services\OrderNotificationService;

class Checkout extends Controller
{
    
    public function checkout(CheckoutRequest $request, CheckoutService $checkoutService, OrderNotificationService $notifications)
    {
        $validated = $request->validated();

        $order = $checkoutService->createOrder(
            auth()->user(),
            $validated + ['is_direct_shipping' => $request->boolean('is_direct_shipping')],
            array_values((array) session()->get('cart', []))
        );
        $isNewOrder = $order->wasRecentlyCreated;

        session()->forget(['cart', 'checkout_token']);

        if ($isNewOrder) {
            $notifications->dispatch($order);
        }

        return view('pages.orderSuccess', ['order' => $order]);
    }

    
}
