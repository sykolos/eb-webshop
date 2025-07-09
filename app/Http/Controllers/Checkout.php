<?php

namespace App\Http\Controllers;

use App\Mail\SuccesOrder;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User_invoce;
use Illuminate\Http\Request;
use App\Models\User_shipping;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class Checkout extends Controller
{
    
    function succesCheckoutEmail($id)
    {
        $order = Order::with([
            'user',
            'items',
            'items.product',
            'items.product.product_unit', // <-- betöltjük, mert PDF-ben használod
            'user_shipping',
            'user_invoice'
        ])->findOrFail($id);

        $status = ['függőben', 'feldolgozás', 'kiszállítva', 'törölve'];
        $data = ['order' => $order, 'states' => $status, 'id' => $id];

        $pdf = Pdf::loadView('myPDF', $data);

        $email = $order->user->email;

        // Első email a vásárlónak
        Mail::send('vendor.notifications.SuccesOrder', $data, function ($message) use ($data, $pdf, $email) {
            $message->to($email)
                    ->cc('sykolos6@gmail.com')
                    ->subject('Sikeres Rendelés')
                    ->attachData($pdf->output(), 'document.pdf');
        });

        // Második email neked/adminnak
        Mail::send('vendor.notifications.SuccesOrder', $data, function ($message) use ($data, $pdf) {
            $message->to('sykolos6@gmail.com')
                    ->subject('Rendelés érkezett a webshopon keresztül!')
                    ->attachData($pdf->output(), 'document.pdf');
        });
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
