<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Mail\ContactForm;
use App\Mail\SuccesOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    //
    // public function sendMail(){
    //     $name='bob';
    //     Mail::to('janos.kolos1@gmail.com')->send(new SuccesOrder($name));

    //     return view('/');
    // }

    //kapcsolat oldal üzenete
    public function sendContact(Request $request){
        //dd($request->all());
        $this->validate($request,[
            'name'=>'required|max:255',
            'email'=>'required|email',
            'subject'=>'required',
            'message'=>'required|max:255'
          ]);
        $n=$request->name;
        $e=$request->email;
        $s=$request->subject;
        $m=$request->message;


        Mail::to('noreply@electrobusiness.hu')->send(new ContactForm($n,$e,$s,$m));
        
        
        return view('pages.contactSucces');
    }
    //rendelés leadáskor email mindkét félnek
    //megkapja a rendelési listát akár a pdf-et?
    

}
