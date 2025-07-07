<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User_invoce;
use Illuminate\Http\Request;
use App\Models\User_shipping;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Barryvdh\DomPDF;

class AccountpageController extends Controller
{
    
    public function orders(){
            return view('pages.components.account.orders');        
    }
    public function userinfo(){
        return view('pages.components.account.userinfo');        
    }
    public function details($id){
        $status=['függőben','feldolgozás','kiszállítva','törölve'];
        $order=Order::with('user','items','items.product')->findOrFail($id);
        // $ordered_products=Items::with
        
        
        return view('pages.components.account.details',['order'=>$order,'states'=>$status,'id'=>$id]);
   
   
    }
    public function modify(){
        return view('pages.components.account.modify');        
    }
    
    public function edit(Request $request) {
    $request->validate([
        'username'   => 'required|max:255',
        'email'      => 'required|email|max:255',
        'company_name' => 'required|max:191',
        'vatnumber'  => 'required|max:191',
        'country'    => 'required|max:191',
        'state'      => 'required',
        'zipcode'    => 'required',
        'city'       => 'required|max:191',
        'address'    => 'required|max:191',
        'receiver'   => 'required|max:191',
        'phone'      => 'required|max:191',
        's_zipcode'  => 'required|max:191',
        's_city'     => 'required|max:191',
        's_address'  => 'required|max:191',
        'comment'    => 'required|max:191',
    ]);

    $id = auth()->user()->id;

    // Felhasználó frissítése
    User::findOrFail($id)->update([
        'name'  => $request->username,
        'email' => $request->email
    ]);

    // Számlázási adatok – létrehozza, ha nincs
    $user_i = User_invoce::firstOrCreate(
        ['user_id' => $id],
        [ // csak ha új sort kell létrehozni
            'company_name' => '',
            'vatnumber'    => '',
            'country'      => '',
            'state'        => '',
            'zipcode'      => '',
            'city'         => '',
            'address'      => ''
        ]
    );

    $user_i->update([
        'company_name' => $request->company_name,
        'vatnumber'    => $request->vatnumber,
        'country'      => $request->country,
        'state'        => $request->state,
        'zipcode'      => $request->zipcode,
        'city'         => $request->city,
        'address'      => $request->address,
    ]);

    // Szállítási adatok – létrehozza, ha nincs
    $user_s = User_shipping::firstOrCreate(
        ['user_id' => $id],
        [
            'receiver' => '',
            'phone'    => '',
            'zipcode'  => '',
            'city'     => '',
            'address'  => '',
            'comment'  => ''
        ]
    );

    $user_s->update([
        'receiver' => $request->receiver,
        'phone'    => $request->phone,
        'zipcode'  => $request->s_zipcode,
        'city'     => $request->s_city,
        'address'  => $request->s_address,
        'comment'  => $request->comment
    ]);

    return redirect()->route('account.modify')->with('success', 'Adatok frissítve');
}

    public function getpdf($id){
        $order=Order::with('user','items','items.product')->findOrFail($id);
        
        $status=['függőben','feldolgozás','kiszállítva','törölve'];
        $pdf =Pdf::loadView('myPDF',['order'=>$order,'states'=>$status,'id'=>$id]);
        return $pdf->download("document.pdf");
    }
    
    
}
