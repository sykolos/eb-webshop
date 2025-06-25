<?php

namespace App\Http\Controllers;

use App\Models\Color;
use App\Models\Category;
use App\Models\product_unit;
use App\Models\Products;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //adminpanel 

    //display
    public function index(){
        $products=Products::with('category','product_unit')->orderBy('created_at','desc')->get();
        $units=product_unit::all();
        return view('admin.pages.products.index',['products'=>$products,'units'=>$units]);
    }
    
    public function store(Request $request){
        //validate
        $request->validate([
            'title'=>'required|max:255',
            'category_id'=>'required',
            'price'=>'required',
            'unit_id'=>'required',
            'image'=>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'serialnum'=>'required|max:255'
          ]);
        //store
        $image_name = 'products/'.time().rand(0,9999).'.'.$request->image->getClientOriginalExtension();
        $request->image->storeAs('public',$image_name);

        $product = Products::create([
            'serial_number' => $request->serialnum,
            'title' => $request->title,
            'category_id'=>$request->category_id,
            'unit_id'=>$request->unit_id,
            'price'=>$request->price, //10000
            'description'=>$request->description,
            'image'=>$image_name
        ]);

        //return

        return back()->with('success','Termék Feltöltve');
    }
    public function create(){
        $categories = Category::all();
        $units=product_unit::all();
        return view('admin.pages.products.create',['product_units'=>$units,'categories'=>$categories]);
    }
    
    public function update(Request $request,$id){
        $request->validate([
            'title'=>'required|max:255',
            'category_id'=>'required',
            'unit_id'=>'required',
            'price'=>'required',
            'image'=>'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'serialnum'=>'required|max:255'
          ]);

        $product=Products::findOrFail($id);
        
        //store
        $image_name=$product->image;

        if($request->image){
            $image_name = 'products/'.time().rand(0,9999).'.'.$request->image->getClientOriginalExtension();
            $request->image->storeAs('public',$image_name);
        }
        

        $product->update([
            'serial_number'=>$request->serialnum,
            'title' => $request->title,
            'category_id'=>$request->category_id,
            'unit_id'=>$request->unit_id,
            'price'=>$request->price, //10000
            'description'=>$request->description,
            'image'=>$image_name
        ]);

        //return

        return back()->with('success','Termék Frissitve');


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
