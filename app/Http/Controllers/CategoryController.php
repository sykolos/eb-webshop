<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //index
    public function index (){
        $categories = Category::all();
        $products= Products::all();
        return view('admin.pages.categories.index', ['categories'=>$categories,'products'=>$products]);

    }

    public function store(Request $request){
        
        //validate
        $request->validate([
            'name'=>'required|unique:categories|max:255'
        ]);
        //store
        $category= new Category();
        $category->name=$request->name;
        $category->save();

        //return response

        return back()->with('success','Kategória mentve');
    }

    public function destroy($id){
        Category::findOrFail($id)->delete();
        return back()->with('success','Kategória Törölve');

    }
}
