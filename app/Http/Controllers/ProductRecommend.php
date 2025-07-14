<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProductRecommend extends Controller
{
    public function getRecommended()
    {
        $recommended = DB::table('recommended_products')
            ->join('products as source', 'recommended_products.product_id', '=', 'source.id')
            ->join('products as target', 'recommended_products.recommended_id', '=', 'target.id')
            ->select('target.id', 'target.title', 'target.thumbnail', 'target.price')
            ->distinct()
            ->get();

        return response()->json([
            'recommended' => $recommended,
        ]);
    }
    
    public function getSimilarProductsRaw($id)
    {
        $product = \App\Models\Products::findOrFail($id);

        return \App\Models\Products::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['product_unit'])
            ->inRandomOrder()
            ->take(12)
            ->get();
    }

    public function similarProducts($id)
    {
        $recommended = $this->similarProductsRaw($id);

        return response()->json([
            'products' => $recommended
        ]);
    }

    
    public function latestProductsRaw()
    {
        return \App\Models\Products::with('product_unit:id,id,measure')
            ->select('id', 'title', 'serial_number', 'price', 'unit_id', 'image', 'created_at')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
    }

    public function getLatestProducts()
    {
        return response()->json([
            'latest_products' => $this->latestProductsRaw(),
        ]);
    }
    public function topCategoriesRaw()
    {
        return \App\Models\Item::select('categories.id', 'categories.name', DB::raw('SUM(items.quantity) as total'))
        ->join('products', 'items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('total')
        ->limit(8)
        ->get();
    }

    public function getTopCategories()
    {
        return response()->json([
            'top_categories' => $this->topCategoriesRaw(),
        ]);
    }


}
