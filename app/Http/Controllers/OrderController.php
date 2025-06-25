<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    //
    public function index(){
        $orders = Order::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.pages.orders.index',['orders'=>$orders]);

    }
    public function view($id){
        $status=['függőben','feldolgozás','kiszállítva','törölve'];
        $order=Order::with('user','items','items.product')->findOrFail($id);
        return view('admin.pages.orders.view',['order'=>$order,'states'=>$status]);
    }
    public function updateStatus(Request $request, $id){
        Order::findOrFail($id)->update(['status'=>$request->status]);
        return back()->with('success','Rendelés Frissitve');
    }
    public function getpdf($id){
        $order=Order::with('user','items','items.product')->findOrFail($id);
        
        $status=['függőben','feldolgozás','kiszállítva','törölve'];

        $pdf =Pdf::loadView('myPDF',['order'=>$order,'states'=>$status,'id'=>$id]);
        return $pdf->download("document.pdf");
    
    }
}
