<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\Category;
use App\Models\product_unit;
use App\Models\Products;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\AuditLogger;


class ProductController extends Controller
{
    //adminpanel 

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

        $products = $query->orderBy('created_at', 'desc')->paginate(20)->onEachSide(0)->appends($request->query());
        $units = product_unit::all();
        $categories = Category::all();

        // AJAX válasz: résznézet visszaadása
        if ($request->ajax()) {
            return view('admin.pages.products.partials.list', compact('products', 'units'))->render();
        }

        // Teljes oldal render
        return view('admin.pages.products.index', compact('products', 'units', 'categories'));
    }

    public function store(ProductStoreRequest $request, AuditLogger $audit){
        $validated = $request->validated();

        // 2. KÉP KONVERTÁLÁS ÉS MENTÉS
        $filename = 'products/' . Str::uuid() . '.webp'; // Egyedi név
        $image = Image::read($validated['image'])->toWebp(90);
        Storage::disk('public')->put($filename, $image); // Mentés storage/app/public/products/…

        // 3. ADATBÁZISBA MENTÉS
        $product = Products::create([
            'serial_number' => $validated['serialnum'],
            'title' => $validated['title'],
            'category_id'=>$validated['category_id'],
            'unit_id'=>$validated['unit_id'],
            'price'=>$validated['price'],
            'description'=>$validated['description'] ?? null,
            'image' => $filename // pl. "products/abc.webp"
        ]);

        // 4. VISSZAJELZÉS
        $audit->record('product.created', $product, [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'price' => $product->price,
        ]);

        return back()->with('success','Termék Feltöltve');
    }

    public function create(){
        $categories = Category::all();
        $units=product_unit::all();
        return view('admin.pages.products.create',['product_units'=>$units,'categories'=>$categories]);
    }
    
    

    public function update(ProductUpdateRequest $request, $id, AuditLogger $audit)
    {
        $validated = $request->validated();

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
            $webp = Image::read($validated['image'])->toWebp(90);
            Storage::disk('public')->put($filename, $webp);

            $imagePath = $filename;
        }

        // 5. FRISSÍTÉS
        $product->update([
            'serial_number' => $validated['serialnum'],
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'unit_id' => $validated['unit_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
        ]);

        // 6. VISSZAJELZÉS
        $audit->record('product.updated', $product, [
            'title' => $product->title,
            'category_id' => $product->category_id,
            'price' => $product->price,
        ]);

        return back()->with('success', 'Termék frissítve');
    }

    
    public function edit($id){
        $product=Products::findOrFail($id);
        $categories = Category::all();
        $units=product_unit::all();
        return view('admin.pages.products.edit',['product_units'=>$units, 'categories'=>$categories,'product'=>$product]);
    }
    
    public function destroy($id, AuditLogger $audit){
        $product = Products::findOrFail($id);
        $product->delete();
        $audit->record('product.deleted', $product, ['title' => $product->title]);
        return back()->with('success','Termék törölve'); 
    }

    public function getPriceForUser($userId = null)
    {
        $userId = $userId ?? auth()->id();
        $basePrice = $this->price;

        // 1. Get the Category Discount for this user
        // Assumes a 'category_discount' relationship exists on the Product model
        $categoryDiscount = \DB::table('group_discounts')
            ->where('user_id', $userId)
            ->where('category_id', $this->category_id)
            ->first();

        $catDiscPercent = $categoryDiscount?->discount_percent ?? 0;

        // 2. Get the Special Price/Extra Percent for this user
        $special = $this->special_prices->firstWhere('user_id', $userId);

        // 3. Priority 1: If there is a Fix Price, return it immediately
        if ($special && $special->price) {
            return $special->price;
        }

        // 4. Priority 2: Calculate price based on Cat % + Extra %
        $extraDiscPercent = $special?->extra_percent ?? 0;
        $totalDiscount = $catDiscPercent + $extraDiscPercent;

        // Apply discount and round (matching your JS logic)
        if ($totalDiscount > 0) {
            return round($basePrice * (1 - ($totalDiscount / 100)));
        }

        return $basePrice;
    }

}
