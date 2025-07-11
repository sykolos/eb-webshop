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
use App\Models\RecommendedProduct;


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
    
    public function special_prices_show()
    {
        $users = \App\Models\User::all();
        return view('admin.pages.prices.index', [
            'users' => $users,
            'listaok' => false
        ]);
    }
    public function ajaxProductList(Request $request, $userId)
    {
        $perPage = 16;

        $query = Products::with(['special_prices' => function($q) use ($userId) {
            $q->where('user_id', $userId);
        }]);

        // Szűrés keresésre
        if ($search = $request->input('search')) {
            $search = strtolower($search);

            if (strlen($search) >= 3) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(serial_number) LIKE ?', ["%{$search}%"]);
                });
            }
        }

        // Csak ahol van külön ár
        if ($request->has('has_price') && $request->input('has_price')) {
            $query->whereHas('special_prices', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        // Csak ahol nincs külön ár
        if ($request->has('no_price') && $request->input('no_price')) {
            $query->whereDoesntHave('special_prices', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $products = $query->paginate($perPage);

        return response()->json([
            'products' => $products,
            'user_id' => $userId
        ]);
    }


    public function ajaxSetPrice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'product_id' => 'required|integer|exists:products,id',
            'price' => 'required|numeric|min:0'
        ]);

        $price = Special_prices::updateOrCreate(
            ['user_id' => $request->user_id, 'product_id' => $request->product_id],
            ['price' => $request->price]
        );

        return response()->json(['success' => true, 'message' => 'Ár elmentve.']);
    }
    public function ajaxDeletePrice(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'product_id' => 'required|integer'
        ]);

        Special_prices::where('user_id', $request->user_id)
            ->where('product_id', $request->product_id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Ár törölve.']);
    }

    public function recommendedEdit()
    {
        // első betöltésre üres keresés
        $products = Products::orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::pluck('product_id')->toArray();

        return view('admin.pages.recommended.index', compact('products', 'recommendedIds'));
    }

    public function ajaxList(Request $request)
{
    try {
        $query = Products::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                if (is_numeric($q)) {
                    $subQuery->where('serial_number', 'like', $q . '%');
                } else {
                    $subQuery->where('title', 'like', '%' . $q . '%');
                }
            });
        }

        $products = $query->orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::pluck('product_id')->toArray();

        $html = view('admin.pages.recommended.table', compact('products', 'recommendedIds'))->render();

        return response()->json(['html' => $html]);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}



    public function recommendedUpdate(Request $request)
    {
        RecommendedProduct::truncate(); // összes eddigit törli
        if ($request->has('products')) {
            foreach ($request->products as $productId) {
                RecommendedProduct::create(['product_id' => $productId]);
            }
        }
        return redirect()->back()->with('success', 'Ajánlott termékek frissítve!');
    }
    

}
