<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

    /**
     * Visszaadja a top 5 legtöbbet vásárolt kategóriát.
     */
    public function getTopCategories()
    {
        $topCategories = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.id', 'categories.name', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'top_categories' => $topCategories,
        ]);
    }
    public function similarProducts($id)
    {
        $product = \App\Models\Products::findOrFail($id);

        $recommended = \App\Models\Products::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['product_unit'])
            ->inRandomOrder()
            ->take(10)
            ->get();

        return response()->json([
            'products' => $recommended
        ]);
    }

}
