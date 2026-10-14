<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use Illuminate\Http\Request;
use App\Services\AuditLogger;

class CategoryController extends Controller
{
    //index
    public function index (){
        $categories = Category::all();
        $products= Products::all();
        return view('admin.pages.categories.index', ['categories'=>$categories,'products'=>$products]);

    }

    public function store(Request $request, AuditLogger $audit){
        
        //validate
        $request->validate([
            'name'=>'required|unique:categories|max:255'
        ]);
        //store
        $category= new Category();
        $category->name=$request->name;
        $category->save();
        $audit->record('category.created', $category, ['name' => $category->name]);

        //return response

        return back()->with('success','Kategória mentve');
    }

    public function destroy($id, AuditLogger $audit){
        $category = Category::findOrFail($id);
        $category->delete();
        $audit->record('category.deleted', $category, ['name' => $category->name]);
        return back()->with('success','Kategória Törölve');

    }
}
