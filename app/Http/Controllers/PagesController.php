<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    //home
    public function home(){
        return view('pages.home');
    }
    //cart
    public function cart(){
        $id=auth()->user()->id;
        $u_i_d=User_invoce::where('user_id','=',$id)->first();
        $u_s_d=User_shipping::where('user_id','=',$id)->first();
        if(is_null(auth()->user()->email_verified_at)||is_null($u_i_d->company_name)||is_null($u_i_d->address)||is_null($u_i_d->city)||is_null($u_i_d->state)||is_null($u_i_d->zipcode)||is_null($u_i_d->country)||is_null($u_i_d->vatnumber)||is_null($u_s_d->address)||is_null($u_s_d->city)||is_null($u_s_d->zipcode)||is_null($u_s_d->phone)||is_null($u_s_d->receiver)||is_null($u_s_d->comment))
        {
            echo view('pages/no-cart');
        }
        else{
            echo view('pages/cart');
        }
    }
    public function contact(){
        return view('pages.contact');
    }
    public function wishlist(){
        echo view('pages/wishlist');
    }
    public function account(){
            return view('pages.components.account.userinfo');        
    }
    public function impresszum(){
        return view('pages.impresszum');
    }
    public function aszf(){
        return view('pages.aszf');
    }
    public function gyik(){
        return view('pages.gyik');
    }
    public function any(){
        return view('pages.any');
    }
    public function eny(){
        return view('pages.eny');
    }
    public function checkout(){
        $id=auth()->user()->id;
        $user_s=User_shipping::where('user_id','=',$id)->first();
        $user_i=User_invoce::where('user_id','=',$id)->first();
        echo view('pages/checkout',['user_s'=>$user_s,'user_i'=>$user_i]);
    }
    public function orderpage(Request $request){
        $products=null;
        if(request()->category){
            $products=Products::with('category','special_prices')->where("category_id",'=',request()->category)->get();
            
        }
        else{
            $products=Products::with('category','product_unit','special_prices')->orderBy('created_at','desc')->get();
        }
        $categories=Category::all();
        if(!is_null($request->sort) ){
            $sender_url=$request->header('referer');
            $dat=explode("=",explode("?",explode("/",$sender_url)[3])[1]);
            if($dat[0]=="category"){
                $products=Products::with('category','special_prices')->where('category_id',"like",$dat[1])->get();
            
            }
            switch($request->sort){
                case "low_high": $products = $products->sortBy('price');
                case "high_low": $products = $products->sortByDesc('price');
                case "a_to_z": $products = $products->sortBy('title');
                case "z_to_a": $products = $products->sortByDesc('title');
            }
        }
        
        echo view('pages/orderpage', ['products'=>$products,'categories'=>$categories]);
    }
    
    public function success(){
        return "Sikeres megrendelés!";
    }
    public function product($id){
        $product=Products::with('category')->findOrFail($id);
        $unit=product_unit::where('id',$product->unit_id)->first();
        echo view('pages/product',['product'=>$product,'unit'=>$unit]);
    }
    public function search(Request $request){
        $request->validate(
            ['query'=>'required|min:3']
        );

        $query=$request->input('query');

        $products=Products::where('title','like',"%$query%")->orWhere('serial_number','like',"%$query%")->get();
        if(request()->category){
            $categories=Category::all();
            
        }
        else{$categories=Category::all();
        }

        return view('pages/search-result',['products'=>$products,'categories'=>$categories]);
    }
}

