<?php

namespace App\Http\Controllers;

use App\Models\Products;
use App\Models\RecommendedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PricingService;


class ProductRecommend extends Controller
{
    public function getRecommended()
    {
        $recommended = Products::query()
            ->published()
            ->with('product_unit')
            ->whereIn('id', RecommendedProduct::query()->orderBy('sort_order')->orderBy('id')->pluck('product_id'))
            ->get()
            ->values();
        $prices = auth()->check()
            ? app(PricingService::class)->priceMany($recommended, auth()->id())
            : [];

        return response()->json([
            'recommended' => $recommended->map(fn (Products $product) => $this->productPayload(
                $product,
                $prices[(int) $product->id] ?? null
            )),
        ]);
    }
    
    public function getSimilarProductsRaw($id)
    {
        $product = \App\Models\Products::findOrFail($id);

        return \App\Models\Products::published()->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['product_unit'])
            ->inRandomOrder()
            ->take(12)
            ->get();
    }

    public function similarProducts($id)
    {
        $recommended = $this->getSimilarProductsRaw($id);
        $prices = auth()->check()
            ? app(PricingService::class)->priceMany($recommended, auth()->id())
            : [];

        return response()->json([
            'products' => $recommended
                ->map(fn (Products $product) => $this->productPayload(
                    $product,
                    $prices[(int) $product->id] ?? null
                ))
                ->values(),
        ]);
    }

    
    public function latestProductsRaw()
    {
        return \App\Models\Products::published()->with(['product_unit:id,id,measure', 'media'])
            ->select('id', 'title', 'serial_number', 'price', 'unit_id', 'image', 'slug', 'availability_status', 'created_at')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();
    }

    public function getLatestProducts()
    {
        $latest = $this->latestProductsRaw();
        $prices = auth()->check()
            ? app(PricingService::class)->priceMany($latest, auth()->id())
            : [];

        return response()->json([
            'latest_products' => $latest
                ->map(fn (Products $product) => $this->productPayload(
                    $product,
                    $prices[(int) $product->id] ?? null
                ))
                ->values(),
        ]);
    }

    /**
     * Keep product feeds free of every user's special prices.
     * Authenticated shoppers receive only their own calculated price.
     */
    private function productPayload(Products $product, ?array $pricing = null): array
    {
        $payload = $product->only([
            'id',
            'title',
            'serial_number',
            'image',
            'unit_id',
        ]);

        $payload['product_unit'] = $product->product_unit?->only([
            'id',
            'unit',
            'quantity',
            'measure',
        ]);

        if (auth()->check()) {
            $payload['price'] = $pricing['final_price']
                ?? app(PricingService::class)->priceForUser($product, auth()->id());
        }

        return $payload;
    }
    public function topCategoriesRaw()
    {
        $categories = \App\Models\Item::select('categories.id', 'categories.name', DB::raw('SUM(items.quantity) as total'))
            ->join('products', 'items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.is_published', true)
            ->where('products.is_archived', false)
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        foreach ($categories as $category) {
            $attempts = 0;
            $maxAttempts = 5;
            $image = null;

            while ($attempts < $maxAttempts) {
                $randomProduct = \App\Models\Products::published()->where('category_id', $category->id)
                    ->whereNotNull('image')
                    ->inRandomOrder()
                    ->first();

                if (!$randomProduct) {
                    break; // nincs ilyen termék, lépjünk tovább
                }

                $path = storage_path('app/public/' . $randomProduct->image);
                if (file_exists($path)) {
                    $image = $randomProduct->image;
                    break;
                }

                $attempts++;
            }

            $category->image = $image; // ha nem talált, marad null
        }

        return $categories;
    }

    public function getTopCategories()
    {
        return response()->json([
            'top_categories' => $this->topCategoriesRaw(),
        ]);
    }


}
