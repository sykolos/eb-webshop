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
    //
    public function checkout(Request $request){
        if(session()->has('cart')){
            $id=auth()->user()->id;
        $user_s=User_shipping::where('user_id','=',$id)->first();
        $user_i=User_invoce::where('user_id','=',$id)->first();
            
            $order = Order::create([
                'user_id'=>$id,
                'name'=>$user_i->company_name,
                'email'=>auth()->user()->email,
                'phone'=>$user_s->phone,
                'address'=>$user_s->address,
                'city'=>$user_s->city,
                'state'=>"-",
                'zipcode'=>$user_s->zipcode,
                'country'=>"Magyarország",
                'total'=>Cart::totalamount() ,            
                'status'=>'függőben',
            ]);
            foreach (session()->get('cart') as $key => $item) {
                $order->items()->create([
                    'product_id'=>$item['product']['id'],
                    // 'color_id'=>$item['color']['id'],
                    'quantity'=>$item['quantity']*$item['q'],
                ]);
            }
            session()->forget('cart');

            $this->succesCheckoutEmail($order->id);

            return view('pages.orderSuccess',['order'=>$order]);
        }
        else{
            return "Üres a kosarad";
        }
        
    }
    function succesCheckoutEmail($id){
        $order=Order::with('user','items','items.product')->findOrFail($id);        
        $status=['függőben','feldolgozás','kiszállítva','törölve'];
        $data=['order'=>$order,'states'=>$status,'id'=>$id];
        $pdf =Pdf::loadView('myPDF',$data);
        $name=$order->user->name;
        $email=$order->user->email;
        Mail::send('vendor.notifications.SuccesOrder',$data,function($message)use($data,$pdf,$email){
            $message->to($email)
            ->cc('rendelesek@electrobusiness.hu')
            ->subject('Sikeres Rendelés')
            ->attachData($pdf->output(),'document.pdf');
        });
        Mail::send('vendor.notifications.SuccesOrder',$data,function($message)use($data,$pdf,$email){
            $message->to('info@electrobusiness.hu')
            ->subject('Rendelés érkezett a webshopon keresztül!')
            ->attachData($pdf->output(),'document.pdf');
        });

    }
    
}
