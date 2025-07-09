<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user.user_invoice', 'items']);

        // Cégnév szűrés
        if ($request->filled('company_name')) {
            $query->whereHas('user.user_invoice', function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->company_name . '%');
            });
        }

        // Dátum szűrés
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Státusz szűrés
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Összeg szűrés
        if ($request->filled('min_total')) {
            $query->where('total', '>=', $request->min_total);
        }
        if ($request->filled('max_total')) {
            $query->where('total', '<=', $request->max_total);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->query());

        return view('admin.pages.orders.index', compact('orders'));
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
