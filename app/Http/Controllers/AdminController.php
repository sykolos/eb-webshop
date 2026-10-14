<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Products;
use App\Models\product_unit;
use Illuminate\Http\Request;
use App\Models\RecommendedProduct;
use App\Services\AuditLogger;
use App\Services\UnitCatalogService;


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
        $units = product_unit::withCount('products')->orderBy('unit')->get();
        return view('admin.m06.units.index',['units'=>$units]);
    }
    public function unit_store(Request $request, UnitCatalogService $units, AuditLogger $audit)    {
        $data = $request->validate([
            'unit' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'measure' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $unit = $units->create($data);
        $audit->record('unit.created', $unit, ['unit' => $unit->unit, 'measure' => $unit->measure]);

        return back()->with('success','Mennyiségi egység létrehozva');
    }
    public function unit_update(Request $request, int $id, UnitCatalogService $units, AuditLogger $audit){
        $data = $request->validate([
            'unit' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'measure' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $unit = $units->update(product_unit::findOrFail($id), $data);
        $audit->record('unit.updated', $unit, ['unit' => $unit->unit, 'measure' => $unit->measure]);

        return back()->with('success','Mennyiségi egység frissítve');
    }
    public function unit_destroy($id, UnitCatalogService $units, AuditLogger $audit){
        $unit = product_unit::findOrFail($id);
        $name = $unit->unit;
        $units->delete($unit);
        $audit->record('unit.deleted', $unit, ['unit' => $name]);

        return back()->with('success','Sikeresen törölve');
    }    
    
    public function recommendedEdit()
    {
        $products = Products::orderBy('id')->paginate(15);
        $recommendedIds = RecommendedProduct::orderBy('sort_order')->orderBy('id')->pluck('product_id')->toArray();
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
        $recommendedIds = RecommendedProduct::orderBy('sort_order')->orderBy('id')->pluck('product_id')->toArray();

        $html = view('admin.pages.recommended.table', compact('products', 'recommendedIds'))->render();

        return response()->json(['html' => $html]);
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

        foreach ($incomingIds as $sortOrder => $id) {
            RecommendedProduct::updateOrCreate(
                ['product_id' => $id],
                ['sort_order' => $sortOrder]
            );
        }

        return redirect()->back()->with('success', 'Kiemelt termékek frissítve!');
    }

}
