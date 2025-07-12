<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\RecommendedProduct;

class PagesController extends Controller
{
    //home
    public function home()
    {
        $highlighted = RecommendedProduct::with([
            'product' => function ($query) {
                $query->select('id', 'title', 'serial_number', 'price', 'unit_id','image');
            },
            'product.product_unit:id,id,measure'
        ])->get();

        return view('pages.home', compact('highlighted'));
    }

    //cart
    public function cart() {
        
        $user = auth()->user();

        // Kapcsolódó adatok lekérése
        $u_i_d = User_invoce::where('user_id', $user->id)->first();
        $u_s_d = User_shipping::where('user_id', $user->id)->first();

        // Alap ellenőrzés: léteznek-e egyáltalán ezek a rekordok
        if (is_null($user->email_verified_at) || is_null($u_i_d) || is_null($u_s_d)) {
            return view('pages/no-cart');
        }

        // Mezők ellenőrzése
        $invoceFields = [
            $u_i_d->company_name,
            $u_i_d->address,
            $u_i_d->city,
            $u_i_d->state,
            $u_i_d->zipcode,
            $u_i_d->country,
            $u_i_d->vatnumber,
        ];

        // Ha bármelyik hiányzik, akkor is hibás
        if (in_array(null, $invoceFields, true)) {
            return view('pages/no-cart');
        }

        // Minden OK
        return view('pages/cart');
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
    
    public function orderpage(Request $request)
    {
        $query = Products::with('category', 'product_unit', 'special_prices');

        // KIEMELT TERMÉKEK
        if ($request->get('category') === 'highlighted') {
            $highlightedIds = DB::table('recommended_products')
                ->select('product_id')
                ->distinct()
                ->pluck('product_id');

            if ($highlightedIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('id', $highlightedIds);
            }
        }

        elseif ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

       
        $categoryId = $request->category;

        if ($categoryId == 11 && $request->filled('kapcsolo')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->kapcsolo as $value) {
                    if ($value === 'van') {
                        $q->orWhere('description', 'like', '%kapcsoló%');
                    }
                    if ($value === 'nincs') {
                        $q->orWhere('description', 'not like', '%kapcsoló%');
                    }
                }
            });
        }

        if ($categoryId == 7 && $request->filled('ontapados')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->ontapados as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%öntapadós%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%öntapadós%');
                    }
                    if ($value === 'perforalt') {
                        $q->orWhere('description', 'like', '%perforált%');
                    }
                    if ($value === 'toldo') {
                        $q->orWhere(function($s) {
                            $s->where('description', 'like', '%toldó%')->orWhere('description', 'like', '%végzáró%');
                        });
                    }
                }
            });
        }

        if ($categoryId == 14 && $request->filled('oldhato')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->oldhato as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%oldható%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%oldható%');
                    }
                }
            });
        }

        if ($categoryId == 15 && $request->filled('lepesallo')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->lepesallo as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%lépésálló%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%lépésálló%');
                    }
                }
            });
        }

        if ($categoryId == 8 && $request->filled('doboz')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->doboz as $value) {
                    if ($value === 'sullyesztett') {
                        $q->orWhere('description', 'like', '%süllyesztett%');
                    }
                    if ($value === 'falonkivuli') {
                        $q->orWhere('description', 'like', '%falon kívüli%');
                    }
                }
            });
        }

        if ($categoryId == 12 && $request->filled('hossz')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->hossz as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                }
            });
        }

        switch ($request->sort) {
            case 'low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'high_low':
                $query->orderBy('price', 'desc');
                break;
            case 'a_to_z':
                $query->orderBy('title', 'asc');
                break;
            case 'z_to_a':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->appends($request->query());
        $categories = Category::all();

        return view('pages.orderpage', compact('products', 'categories'));
    }

    public function success(){
        return "Sikeres megrendelés!";
    }
    // public function product($id)
    // {
    //     $product = Products::with(['category', 'product_unit', 'special_prices'])->findOrFail($id);
    //     $unit = $product->product_unit;

    //     return view('pages.product', [
    //         'product' => $product,
    //         'unit' => $unit
    //     ]);
    // }
    public function product($id)
    {
        $product = Products::with(['category', 'product_unit', 'special_prices'])->findOrFail($id);
        $unit = $product->product_unit;

        $recommendController = new \App\Http\Controllers\ProductRecommend();
        $similar = $recommendController->getSimilarProductsRaw($product->id);

        return view('pages.product', [
            'product' => $product,
            'unit' => $unit,
            'similar' => $similar,
        ]);
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

