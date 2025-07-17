<?php

namespace App\Http\Controllers;

use App\Mail\SuccesOrder;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Products;
use App\Models\User_invoce;
use Illuminate\Http\Request;
use App\Models\User_shipping;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use App\Mail\SuccesOrderCustomerMail;
use App\Mail\SuccesOrderAdminMail;

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

    public function checkout(Request $request)
    {
        if (!session()->has('cart')) {
            return "Üres a kosarad";
        }

        $id = auth()->id();

        // 1. Validáció
        $validated = $request->validate([
            'shipping_address_id' => 'required|exists:user_shippings,id',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:0,1',
        ]);

        // 2. Lekérjük a kiválasztott szállítási címet (csak ha tényleg az övé)
        $user_s = User_shipping::where('id', $validated['shipping_address_id'])
            ->where('user_id', $id)
            ->firstOrFail();

        // 3. Számlázási adatok betöltése
        $user_i = User_invoce::where('user_id', $id)->first();

        // 4. Rendelés mentése
        $order = Order::create([
            'user_id' => $id,
            'shipping_address_id' => $user_s->id, // új mező
            'name' => $user_i->company_name,
            'email' => auth()->user()->email,
            'phone' => $user_s->phone,
            'address' => $user_s->address,
            'city' => $user_s->city,
            'state' => '-', // ha nincs megadva
            'zipcode' => $user_s->zipcode,
            'country' => 'Magyarország',
            'total' => Cart::totalamount(),
            'status' => 'függőben',
            'note' => $validated['note'] ?? null,
            'payment_method' => (bool)$request->payment_method,
            'is_direct_shipping' => $request->has('is_direct_shipping')
        ]);

        // 5. Termékek rendeléshez csatolása
        foreach (session()->get('cart') as $item) {
            
            $order->items()->create([
                'product_id' => $item['product']['id'],
                'quantity' => $item['quantity'] * $item['q'],
                'unit_price' => Products::with('special_prices')->find($item['product']['id'])->getPriceForUser(), // <- EZ ITT A LÉNYEG
            ]);
        }

        // 6. Kosár törlése
        session()->forget('cart');

        // 7. Email + PDF küldés
        $this->succesCheckoutEmail($order->id);

        // 8. Sikeres nézet
        return view('pages.orderSuccess', ['order' => $order]);
    }

    
}
