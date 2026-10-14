<?php

namespace App\Http\Controllers;

use App\Mail\SuccesOrder;
use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Mail\SuccesOrderCustomerMail;
use App\Mail\SuccesOrderAdminMail;
use Throwable;

class Checkout extends Controller
{
    
    public function succesCheckoutEmail($id)
    {
        $order = Order::with([
            'user',
            'items',
            'items.product',
            'items.product.product_unit',
            'user_shipping',
            'user_invoice'
        ])->findOrFail($id);

        $status = ['függőben', 'feldolgozás', 'kiszállítva', 'törölve'];
        $data = ['order' => $order, 'states' => $status, 'id' => $id];

        // PDF generálás
        $pdf = Pdf::loadView('myPDF', $data);

        // Egyedi fájlnév
        $filename = 'EBR-2025-' . $order->id . '.pdf';

        $email = $order->user->email;

        // 1. Email a vásárlónak
        Mail::to($order->user->email)
            ->cc('noreply@electrobusiness.hu')
            ->send(new SuccesOrderCustomerMail($order, $status, $id));

        // 2. Email neked/adminnak
            Mail::to('info@electrobusiness.hu')
        ->send(new SuccesOrderAdminMail($order, $status, $id));
    }

    public function checkout(Request $request, CheckoutService $checkoutService)
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required|integer',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:0,1',
            'checkout_token' => 'required|uuid',
        ]);

        $order = $checkoutService->createOrder(
            auth()->user(),
            $validated + ['is_direct_shipping' => $request->boolean('is_direct_shipping')],
            array_values((array) session()->get('cart', []))
        );
        $isNewOrder = $order->wasRecentlyCreated;

        session()->forget(['cart', 'checkout_token']);

        if ($isNewOrder) {
            try {
                $this->succesCheckoutEmail($order->id);
            } catch (Throwable $exception) {
                Log::error('A rendelési értesítések küldése sikertelen.', [
                    'order_id' => $order->id,
                    'exception' => $exception,
                ]);
            }
        }

        return view('pages.orderSuccess', ['order' => $order]);
    }

    
}
