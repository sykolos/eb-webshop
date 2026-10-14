<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use App\Models\product_unit;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\RecommendedProduct;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Http\Controllers\ProductRecommend;
use App\Services\CategoryTreeService;
use App\Services\FilterCatalogService;
use App\Services\PricingService;

class PagesController extends Controller
{
    //home
    public function home(PricingService $pricing)
    {
        $highlighted = RecommendedProduct::query()->orderBy('sort_order')->orderBy('id')->with([
            'product' => function ($query) {
                $query->published()->select('id', 'title', 'serial_number', 'price', 'unit_id', 'image', 'slug', 'availability_status');
            },
            'product.product_unit:id,id,measure'
        ])->get();

        $recommend = new ProductRecommend();

        $latestProducts = $recommend->latestProductsRaw();

        if (auth()->check()) {
            $homeProducts = $highlighted->pluck('product')->filter()->merge($latestProducts);
            $priceMap = $pricing->priceMany($homeProducts, auth()->id());

            $homeProducts->each(function (Products $product) use ($priceMap) {
                $product->setAttribute('pricing', $priceMap[(int) $product->id] ?? null);
            });
        }

        $topCategories = $recommend->topCategoriesRaw(); // ezt is hozzuk létre
        foreach ($latestProducts as $i => $product) {
            if (!$product->id) {
                dd("HIBA: $i. terméknek nincs ID-je", $product->toArray());
            }
        }
        return view('pages.home', compact('highlighted', 'latestProducts', 'topCategories'));
    }


    //cart
    public function cart() {
        
        $user = auth()->user();

        // Kapcsolódó adatok lekérése
        $u_i_d = User_invoce::where('user_id', $user->id)->first();
        $u_s_d = User_shipping::where('user_id', $user->id)->first();

        // Alap ellenőrzés: léteznek-e egyáltalán ezek a rekordok
        if (is_null($user->email_verified_at) || is_null($u_i_d) || is_null($u_s_d)) {
            return view('pages/no-cart');
        }

        // Mezők ellenőrzése
        $invoceFields = [
            $u_i_d->company_name,
            $u_i_d->address,
            $u_i_d->city,
            $u_i_d->state,
            $u_i_d->zipcode,
            $u_i_d->country,
            $u_i_d->vatnumber,
        ];

        // Ha bármelyik hiányzik, akkor is hibás
        if (in_array(null, $invoceFields, true)) {
            return view('pages/no-cart');
        }

        // Minden OK
        return view('pages/cart');
    }

    public function contact(){
        return view('pages.contact');
    }
    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string',
        ]);

        Mail::to('info@electrobusiness.hu')
            ->send(new ContactFormMail($data));

        return back()->with('success', 'Üzenetedet sikeresen elküldtük!');
    }
    public function wishlist(){
        echo view('pages/wishlist');
    }
    public function account(){
            return view('pages.components.account.userinfo');        
    }
    public function impresszum(){
        return view('pages.impresszum');
    }
    public function aszf(){
        return view('pages.aszf');
    }
    public function gyik(){
        return view('pages.gyik');
    }
    public function any(){
        return view('pages.any');
    }
    public function eny(){
        return view('pages.eny');
    }
    public function checkout(){
        $id=auth()->user()->id;
        $user_s=User_shipping::where('user_id','=',$id)->first();
        $user_i=User_invoce::where('user_id','=',$id)->first();
        $checkout_token = session()->get('checkout_token') ?: (string) Str::uuid();
        session()->put('checkout_token', $checkout_token);
        echo view('pages/checkout', compact('user_s', 'user_i', 'checkout_token'));
    }
    
    public function orderpage(Request $request, CategoryTreeService $tree, FilterCatalogService $filters, PricingService $pricing)
    {
        $query = Products::published()->with('category', 'product_unit');
        $activeCategory = $this->resolveShopCategory($request->get('category'));

        // KIEMELT TERMÉKEK
        if ($request->get('category') === 'highlighted') {
            $highlightedIds = DB::table('recommended_products')
                ->select('product_id')
                ->distinct()
                ->pluck('product_id');

            if ($highlightedIds->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('id', $highlightedIds);
            }
        }
        elseif ($request->get('category') === 'latest') {
            $latestProducts = app(ProductRecommend::class)->latestProductsRaw();
            
            if ($latestProducts->isEmpty()) {
                $query->whereRaw('0 = 1');
            } else {
                $query->whereIn('id', $latestProducts->pluck('id'));
            }
        }

        elseif ($activeCategory) {
                $query->whereIn('category_id', $tree->categoryAndDescendantIds((int) $activeCategory->id));
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                ->orWhere('serial_number', 'like', "%{$request->search}%")
                ->orWhere('manufacturer', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if (in_array($request->get('availability'), Products::availabilityStatuses(), true)) {
            $query->where('availability_status', $request->get('availability'));
        }

        foreach ((array) $request->get('filter', []) as $valueIds) {
            $valueIds = array_values(array_filter(array_map('intval', (array) $valueIds)));
            if ($valueIds !== []) {
                $query->whereHas('filterValues', fn ($filterQuery) => $filterQuery->whereIn('filter_values.id', $valueIds));
            }
        }

       
        $categoryId = $activeCategory?->id ?? $request->category;

        if ($categoryId == 11 && $request->filled('kapcsolo')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->kapcsolo as $value) {
                    if ($value === 'van') {
                        $q->orWhere('description', 'like', '%kapcsoló%');
                    }
                    if ($value === 'nincs') {
                        $q->orWhere('description', 'not like', '%kapcsoló%');
                    }
                }
            });
        }

        if ($categoryId == 7 && $request->filled('ontapados')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->ontapados as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%öntapadós%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%öntapadós%');
                    }
                    if ($value === 'perforalt') {
                        $q->orWhere('description', 'like', '%perforált%');
                    }
                    if ($value === 'toldo') {
                        $q->orWhere(function($s) {
                            $s->where('description', 'like', '%toldó%')->orWhere('description', 'like', '%végzáró%');
                        });
                    }
                }
            });
        }

        if ($categoryId == 14 && $request->filled('oldhato')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->oldhato as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%oldható%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%oldható%');
                    }
                }
            });
        }

        if ($categoryId == 15 && $request->filled('lepesallo')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->lepesallo as $value) {
                    if ($value === 'igen') {
                        $q->orWhere('description', 'like', '%lépésálló%');
                    }
                    if ($value === 'nem') {
                        $q->orWhere('description', 'not like', '%lépésálló%');
                    }
                }
            });
        }

        if ($categoryId == 8 && $request->filled('doboz')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->doboz as $value) {
                    if ($value === 'sullyesztett') {
                        $q->orWhere('description', 'like', '%süllyesztett%');
                    }
                    if ($value === 'falonkivuli') {
                        $q->orWhere('description', 'like', '%falon kívüli%');
                    }
                }
            });
        }

        if ($categoryId == 12 && $request->filled('hossz')) {
            $query->where(function ($q) use ($request) {
                foreach ((array) $request->hossz as $value) {
                    $q->orWhere('title', 'like', "%{$value}%");
                }
            });
        }

        switch ($request->sort) {
            case 'low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'high_low':
                $query->orderBy('price', 'desc');
                break;
            case 'a_to_z':
                $query->orderBy('title', 'asc');
                break;
            case 'z_to_a':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12)->onEachSide(0)->appends($request->query());
        $this->attachPricing($products, $pricing);
        $categories = $tree->flatten($tree->roots(true));
        $filterDefinitions = $filters->definitionsForCategory($activeCategory);
        $breadcrumbs = $this->shopBreadcrumbs($activeCategory);
        $viewMode = $request->get('view', session('shop_view', 'normal'));

        return view('pages.orderpage', compact('products', 'categories', 'filterDefinitions', 'activeCategory', 'breadcrumbs', 'viewMode'));
    }
    public function quickProductsAjax(Request $request, CategoryTreeService $tree, PricingService $pricing)
    {
        $query = Products::published()->with('product_unit');

        // Kiemelt termékek
        if ($request->get('category') === 'highlighted') {
            $highlightedIds = DB::table('recommended_products')
                ->select('product_id')
                ->distinct()
                ->pluck('product_id');

            $query->whereIn('id', $highlightedIds->isEmpty() ? [0] : $highlightedIds);
        }

        // Új termékek
        elseif ($request->get('category') === 'latest') {
            $latestProducts = app(ProductRecommend::class)->latestProductsRaw();
            $query->whereIn('id', $latestProducts->isEmpty() ? [0] : $latestProducts->pluck('id'));
        }

        // Normál kategória ID
        elseif ($request->filled('category')) {
            $query->whereIn('category_id', $tree->categoryAndDescendantIds((int) $request->category));
        }

        // Keresés
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                ->orWhere('serial_number', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        // Lapozás + szűrés megtartása
        $products = $query->orderBy('created_at', 'desc')
                        ->paginate(12)
                        ->onEachSide(0)
                        ->appends($request->all());
        $this->attachPricing($products, $pricing);

        // HTML + lapozás
        $html = view('pages.components.shop.partials.quick-product-boxes', compact('products'))->render();

        // Infószöveg összeállítás
        $info = $products->total() . ' db termék';

        if ($request->filled('search')) {
            $info .= ' a következőre: „' . e($request->search) . '”';
        }

        if ($request->filled('category')) {
            $categoryText = match ($request->category) {
                'latest' => 'Új termékeink',
                'highlighted' => 'Kiemelt termékeink',
                default => optional(Category::find($request->category))->name
            };

            if ($categoryText) {
                $info .= ' kategóriában: „' . $categoryText . '”';
            }
        }

        return response()->json([
            'html' => $html,
            'info' => $info
        ]);
    }


    public function success(){
        return "Sikeres megrendelés!";
    }
    
    public function product($id, PricingService $pricing)
    {
        $product = Products::published()->with([
            'category', 'product_unit', 'media', 'filterValues.definition',
        ])->findOrFail($id);
        $this->attachPricing(collect([$product]), $pricing);
        $unit = $product->product_unit;

        $recommendController = new \App\Http\Controllers\ProductRecommend();
        $similar = $recommendController->getSimilarProductsRaw($product->id);
        $this->attachPricing($similar, $pricing);

        return view('pages.product', [
            'product' => $product,
            'unit' => $unit,
            'similar' => $similar,
        ]);
    }

    public function productBySlug(string $slug, PricingService $pricing)
    {
        $product = Products::published()->where('slug', $slug)->firstOrFail();

        return $this->product($product->id, $pricing);
    }

    public function search(Request $request){
        $request->validate(
            ['query'=>'required|min:3']
        );

        $query=$request->input('query');

        return redirect()->route('orderpage', ['search' => $query]);
    }

    public function autocomplete(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = Products::published()
            ->where(fn ($builder) => $builder
                ->where('title', 'like', '%'.$query.'%')
                ->orWhere('serial_number', 'like', '%'.$query.'%'))
            ->orderByRaw('CASE WHEN serial_number LIKE ? THEN 0 ELSE 1 END', [$query.'%'])
            ->orderBy('title')
            ->limit(8)
            ->get(['id', 'slug', 'title', 'serial_number', 'image'])
            ->map(fn (Products $product) => [
                'id' => $product->id,
                'title' => $product->title,
                'serial_number' => $product->serial_number,
                'image' => $product->image,
                'url' => $product->slug
                    ? route('product.slug', $product->slug)
                    : route('product', $product->id),
            ]);

        return response()->json(['results' => $results]);
    }

    public function quickOrder(Request $request)
    {
        return redirect()->route('orderpage', ['view' => 'quick']);
    }

    private function resolveShopCategory(mixed $value): ?Category
    {
        if (!$value || in_array($value, ['latest', 'highlighted'], true)) {
            return null;
        }

        return is_numeric($value)
            ? Category::query()->active()->find((int) $value)
            : Category::query()->active()->where('slug', $value)->first();
    }

    private function shopBreadcrumbs(?Category $category): array
    {
        if (!$category) {
            return [];
        }

        $categories = Category::whereIn('id', app(CategoryTreeService::class)->ancestorIds($category->id))
            ->get()->keyBy('id');

        return collect(app(CategoryTreeService::class)->ancestorIds($category->id))
            ->reverse()
            ->map(fn ($id) => $categories->get($id))
            ->filter()
            ->values()
            ->all();
    }

    private function attachPricing(iterable $products, PricingService $pricing): void
    {
        if (!auth()->check()) {
            return;
        }

        $collection = method_exists($products, 'getCollection')
            ? $products->getCollection()
            : collect($products);
        $priceMap = $pricing->priceMany($collection, auth()->id());

        $collection->each(function (Products $product) use ($priceMap) {
            $product->setAttribute('pricing', $priceMap[(int) $product->id] ?? null);
        });
    }
}

