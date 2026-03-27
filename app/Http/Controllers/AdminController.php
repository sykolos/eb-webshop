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
    public function users(Request $request){
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->onEachSide(0);

        if ($request->ajax()) {
            return view('admin.pages.users.partials.list', compact('users'))->render();
        }

        return view('admin.pages.users.index', compact('users'));
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
        $categories = \App\Models\Category::all();

        return view('admin.pages.prices.index', [
            'users' => $users,
            'categories' => $categories,
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

        $products = $query->paginate($perPage)->onEachSide(0);

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
        $products = Products::orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::pluck('product_id')->toArray();
        $recommendedProducts = Products::whereIn('id', $recommendedIds)->get();

        // Itt készítjük elő a JS-ben használt adatokat
        $recommendedData = $recommendedProducts->keyBy('id')->map(function ($item) {
            return [
                'id' => $item->id,
                'serial_number' => $item->serial_number,
                'title' => $item->title,
            ];
        });

        return view('admin.pages.recommended.index', compact(
            'products',
            'recommendedIds',
            'recommendedProducts',
            'recommendedData'
        ));
    }

    public function ajaxList(Request $request)
    {
        $query = Products::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($subQuery) use ($q) {
                $subQuery->where('serial_number', 'like', $q . '%')
                        ->orWhere('title', 'like', '%' . $q . '%');
            });
        }

        $products = $query->orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::pluck('product_id')->toArray();

        $html = view('admin.pages.recommended.table', compact('products', 'recommendedIds'))->render();

        return response()->json(['html' => $html]);
    }

    public function productSelectSearch(Request $request)
    {
        $search = $request->input('search');

        $query = Products::query();
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('title')->limit(30)->get();

        return response()->json(['results' => $products->map(function ($product) {
            return ['id' => $product->id, 'text' => $product->title . ' (' . $product->serial_number . ')'];
        })]);
    }

    public function productUsersAjax(Request $request, $productId)
    {
        $query = User::with(['special_prices' => function ($q) use ($productId) {
            $q->where('product_id', $productId);
        }]);

        if ($request->has('search') && strlen($request->search) >= 2) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('only_modified') && $request->only_modified == 1) {
            $query->whereHas('special_prices', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        $users = $query->orderBy('name')->paginate(20);

        return response()->json(['users' => $users]);
    }

    public function recommendedUpdate(Request $request)
    {
        \Log::info('--- Kiemelt termékek mentés indul ---');
        \Log::info('products raw:', $request->all());

        $incomingIds = collect($request->input('products', []))
            ->filter(fn($id) => $id !== '__empty__')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        \Log::info('products integer IDs:', $incomingIds);

        $currentIds = RecommendedProduct::pluck('product_id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        \Log::info('jelenlegi recommended DB-ben:', $currentIds);

        $idsToDelete = array_diff($currentIds, $incomingIds);

        \Log::info('Törlendő ID-k:', $idsToDelete);

        if (!empty($idsToDelete)) {
            RecommendedProduct::whereIn('product_id', $idsToDelete)->delete();
        }

        foreach ($incomingIds as $id) {
            RecommendedProduct::firstOrCreate(['product_id' => $id]);
        }

        return redirect()->back()->with('success', 'Kiemelt termékek frissítve!');
    }

    public function matrixAjax(Request $request, $userId)
    {
        $query = Products::with(['special_prices' => function($q) use ($userId) {
            $q->where('user_id', $userId);
        }]);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                ->orWhere('serial_number', 'like', "%{$request->search}%");
            });
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->only_modified == 1) {
            $query->where(function($q) use ($userId) {
                $q->whereHas('special_prices', function($sq) use ($userId) {
                    $sq->where('user_id', $userId);
                })
                ->orWhereIn('category_id', function($subquery) use ($userId) {
                    $subquery->select('category_id')
                            ->from('group_discounts')
                            ->where('user_id', $userId)
                            ->where(function($dq) {
                                $dq->where('discount_percent', '>', 0)
                                    ->orWhere('extra_percent', '>', 0);
                            });
                });
            });
        }

        $products = $query->paginate(20);

        foreach ($products as $product) {
            $discount = \DB::table('group_discounts')
                ->where('user_id', $userId)
                ->where('category_id', $product->category_id)
                ->first();

            $product->category_discount = [
                'discount_percent' => $discount->discount_percent ?? 0
            ];
        }

        return response()->json(['products' => $products]);
    }

public function saveMatrix(Request $request)
{
    \Log::info('SaveMatrix Payload:', $request->all());

    \DB::table('group_discounts')->updateOrInsert(
        ['user_id' => $request->user_id, 'category_id' => $request->category_id],
        [
            'discount_percent' => $request->discount_percent ?? 0,
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

    $hasFixPrice = !empty($request->fix_price);
    $hasExtraPercent = !empty($request->extra_percent) && $request->extra_percent != 0;

    if ($hasFixPrice || $hasExtraPercent) {
        \DB::table('special_prices')->updateOrInsert(
            ['user_id' => $request->user_id, 'product_id' => $request->product_id],
            [
                'price' => $request->fix_price ?: 0, 
                'extra_percent' => $request->extra_percent ?? 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    } else {
        if ($request->product_id) {
            \DB::table('special_prices')
                ->where('user_id', $request->user_id)
                ->where('product_id', $request->product_id)
                ->delete();
        }
    }

    return response()->json(['success' => true]);
}

    public function bulkUpdateCategoryForAll(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'discount_percent' => 'required|numeric|min:0|max:100',
        ]);

        $categoryId = $request->category_id;
        $percent = $request->discount_percent;

        // 1. Get all User IDs from your users table
        $userIds = \DB::table('users')->pluck('id');

        // 2. Prepare the data for the group_discounts table
        $now = now();
        $data = $userIds->map(function($userId) use ($categoryId, $percent, $now) {
            return [
                'user_id' => $userId,
                'category_id' => $categoryId,
                'discount_percent' => $percent,
                'updated_at' => $now,
                // 'created_at' => $now, // Include if your table uses it
            ];
        })->toArray();

        // 3. Use upsert to handle "Insert if new, Update if exists"
        // Note: This requires a unique index on [user_id, category_id] in group_discounts
        \DB::table('group_discounts')->upsert(
            $data, 
            ['user_id', 'category_id'], 
            ['discount_percent', 'updated_at']
        );

        return response()->json([
            'success' => true, 
            'message' => "Sikeresen beállítva $percent% kedvezmény minden felhasználónak."
        ]);
    }
}
