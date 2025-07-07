<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\product_unit;
use Illuminate\Http\Request;
use App\Models\Special_prices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class AdminController extends Controller
{
    //dashboard
    public function dashboard(){
        $products=Products::all();
        $orders_0=Order::where('status','like','kiszállítva')->orWhere('status','like','függőben');
        $orders_1=Order::where('status','=','kiszállítva');        
        $orders_2=Order::where('status','=','törölve');                
        return view('admin.pages.dashboard',['products'=>$products,'orders_0'=>$orders_0,'orders_1'=>$orders_1,'orders_2'=>$orders_2]);
    }
    public function users(){
        $users= User::all();
        return view('admin.pages.users.index',['users'=>$users]);
    }
    public function user_store(){
        //létrehozni
    }
    public function user_view($id)    {
        $user=User::findOrFail($id);
        return view('admin.pages.users.view',['user'=>$user]);
    }
    public function user_destroy($id)    {
        User::findOrFail($id)->delete();
        return back()->with('success','Sikeresen törölve');
    }
    public function units(){
        $units = product_unit::all();
        return view('admin.pages.units.index',['units'=>$units]);
    }
    public function unit_store(Request $request)    {        
        //validate
        $request->validate([
            'unit'=>'required||max:255',
            'quantity'=>'required|numeric|min:1|max:500',
            'measure'=>'required||max:255'
        ]);
        //store
        $unit= new product_unit();
        $unit->unit=$request->unit;
        $unit->quantity=$request->quantity;
        $unit->measure=$request->measure;
        $unit->save();

        //return response
        return back()->with('success','Mennyiségi egység létrehozva');
    }
    public function unit_destroy($id){
        product_unit::findOrFail($id)->delete();
        return back()->with('success','Sikeresen törölve');
    }

    
    public function special_prices_show(){
        $users=User::all();
        
        $listaok=false;
        return view('admin.pages.prices.index',['listaok'=>$listaok,'users'=>$users]);
    }

     public function special_price_check( $id){   
        $listaok=true;


        //     $listaok=true; 
        $cserevan=false;            
        $users=User::all();
        // $products=Products::with('special_prices')->orderBy('created_at','desc')->get();
        $query="select p.id as id ,p.serial_number as serial, p.title as name, COALESCE(sp.price, p.price) AS price FROM products p LEFT JOIN special_prices sp ON p.ID = sp.product_id AND sp.user_id =$id order by p.id;";
        $products=DB::select($query);
        
        return view('admin.pages.prices.index',['listaok'=>$listaok,'cserevan'=>$cserevan,'users'=>$users,'products'=>$products,'id'=>$id]);
     }

     public function special_price_destroy($id,$pid){
         Special_prices::where('user_id','=',$id)->where('product_id','=',$pid)->delete();
         return back()->with('success','Sikeresen törölve');
     }
    public function special_price_change( $id,$pid){   
        $listaok=true;     
        $cserevan=true;
        $users=User::all();
        $query="select p.id as id ,p.serial_number as serial, p.title as name, COALESCE(sp.price, p.price) AS price FROM products p LEFT JOIN special_prices sp ON p.ID = sp.product_id AND sp.user_id =$id order by p.id;";
        
        $products=DB::select($query);
        $query="select p.id as id ,p.serial_number as serial, p.title as name, COALESCE(sp.price, p.price) AS price FROM products p LEFT JOIN special_prices sp ON p.ID = sp.product_id AND sp.user_id =$id where p.id=$pid order by p.id; ";
        $csereproduct=DB::select($query);
        return view('admin.pages.prices.index',['listaok'=>$listaok,'cserevan'=>$cserevan,'users'=>$users,'products'=>$products,'id'=>$id,'pid'=>$pid,'csereproduct'=>$csereproduct]);
    }

    public function special_price_set($id,$pid,Request $request){
        $listaok=true;  
        $cserevan=false;
        $users=User::all();
        $query="select p.id as id ,p.serial_number as serial, p.title as name, COALESCE(sp.price, p.price) AS price FROM products p LEFT JOIN special_prices sp ON p.ID = sp.product_id AND sp.user_id =$id order by p.id;";
        
        $products=DB::select($query);
        $query2="select products.id as id from products inner join special_prices on products.id = special_prices.product_id where special_prices.product_id=$pid and special_prices.user_id=$id; ";
        $query3=DB::select($query2 );
        
        $request->validate([
            'price'=>'required',
          ]);

        if(empty($query3)){
            $specprice = Special_prices::create([
                'user_id'=>$id,
                'product_id'=>$pid,
                'price'=>$request->price,
            ]);
            return view('admin.pages.prices.index',['listaok'=>$listaok,'cserevan'=>$cserevan,'users'=>$users,'products'=>$products,'id'=>$id,'pid'=>$pid])->with('succes',"Az ár cserélve.");

        }
        else{
            $specprice=Special_prices::where('user_id',$id)->where('product_id',$pid)->first();
            $specprice->update([
                'price'=>$request->price
            ]);
            return back()->with('succes',"Az ár cserélve.");
        }
            
            
    
        

     }
}
