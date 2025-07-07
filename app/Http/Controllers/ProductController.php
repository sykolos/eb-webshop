<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\Category;
use App\Models\product_unit;
use App\Models\Products;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    //adminpanel 

    //display
    // public function index()
    // {
    //     $products = Products::with('category', 'product_unit')
    //         ->orderBy('created_at', 'desc')
    //         ->paginate(20); // vagy amennyi neked kényelmes

    //     $units = product_unit::all();

    //     return view('admin.pages.products.index', [
    //         'products' => $products,
    //         'units' => $units
    //     ]);
    // }
    public function index(Request $request)
    {
        $query = Products::with(['category', 'product_unit']);

        // Terméknév és cikkszám szűrés
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }


        // Ár szűrés
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Kategória szűrés
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Létrehozás dátum szűrés
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        $units = product_unit::all();
        $categories = Category::all(); // kategória dropdownhoz

        return view('admin.pages.products.index', compact('products', 'units', 'categories'));
    }

    public function store(Request $request){
        // 1. VALIDÁCIÓ
        $request->validate([
            'title'=>'required|max:255',
            'category_id'=>'required',
            'price'=>'required',
            'unit_id'=>'required',
            'image'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'serialnum'=>'required|max:255'
        ]);

        // 2. KÉP KONVERTÁLÁS ÉS MENTÉS
        $filename = 'products/' . Str::uuid() . '.webp'; // Egyedi név
        $image = Image::make($request->file('image'))->encode('webp', 90);
        Storage::disk('public')->put($filename, $image); // Mentés storage/app/public/products/…

        // 3. ADATBÁZISBA MENTÉS
        $product = Products::create([
            'serial_number' => $request->serialnum,
            'title' => $request->title,
            'category_id'=>$request->category_id,
            'unit_id'=>$request->unit_id,
            'price'=>$request->price,
            'description'=>$request->description,
            'image' => $filename // pl. "products/abc.webp"
        ]);

        // 4. VISSZAJELZÉS
        return back()->with('success','Termék Feltöltve');
    }

    public function create(){
        $categories = Category::all();
        $units=product_unit::all();
        return view('admin.pages.products.create',['product_units'=>$units,'categories'=>$categories]);
    }
    
    

    public function update(Request $request, $id)
    {
        // 1. VALIDÁCIÓ
        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required',
            'unit_id' => 'required',
            'price' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'serialnum' => 'required|max:255',
        ]);

        // 2. TERMÉK LEKÉRÉSE
        $product = Products::findOrFail($id);

        // 3. ALAPÉRTELMEZETT KÉP: meglévő kép elérési útja
        $imagePath = $product->image;

        // 4. HA VAN ÚJ KÉP, konvertáljuk WebP-re és felülírjuk
        if ($request->hasFile('image')) {
            // (opcionálisan: régit törölheted)
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Új WebP név és mentés
            $filename = 'products/' . Str::uuid() . '.webp';
            $webp = Image::make($request->file('image'))->encode('webp', 90);
            Storage::disk('public')->put($filename, $webp);

            $imagePath = $filename;
        }

        // 5. FRISSÍTÉS
        $product->update([
            'serial_number' => $request->serialnum,
            'title' => $request->title,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'price' => $request->price,
            'description' => $request->description,
            'image' => $imagePath,
        ]);

        // 6. VISSZAJELZÉS
        return back()->with('success', 'Termék frissítve');
    }

    
    public function edit($id){
        $product=Products::findOrFail($id);
        $categories = Category::all();
        $units=product_unit::all();
        return view('admin.pages.products.edit',['product_units'=>$units, 'categories'=>$categories,'product'=>$product]);
    }
    
    public function destroy($id){
        Products::findOrFail($id)->delete();
        return back()->with('success','Termék törölve'); 
    }
}
