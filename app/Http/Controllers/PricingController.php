<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Products;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PricingAdminService;
use Illuminate\Http\Request;

final class PricingController extends Controller
{
    public function __construct(
        private readonly PricingAdminService $pricingAdmin,
    ) {
    }

    public function index()
    {
        return view('admin.pages.prices.index', [
            'users' => User::query()->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('name')->get(),
            'listaok' => false,
        ]);
    }

    public function matrix(Request $request, User $user)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'only_modified' => ['nullable', 'boolean'],
        ]);

        return response()->json([
            'products' => $this->pricingAdmin->matrix($user, $filters),
            'user_id' => $user->id,
        ]);
    }

    public function productUsers(Request $request, Products $product)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'only_modified' => ['nullable', 'boolean'],
        ]);

        $query = User::query()->with(['special_prices' => fn ($builder) => $builder->where('product_id', $product->id)]);

        if (filled($filters['search'] ?? null)) {
            $search = trim((string) $filters['search']);
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%'));
        }

        if (!empty($filters['only_modified'])) {
            $query->whereHas('special_prices', fn ($builder) => $builder->where('product_id', $product->id));
        }

        return response()->json(['users' => $query->orderBy('name')->paginate(20)->onEachSide(0)]);
    }

    public function productSearch(Request $request)
    {
        $search = trim((string) $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
        ])['search'] ?? '');

        return response()->json([
            'results' => Products::query()
                ->when($search !== '', fn ($query) => $query->where(fn ($builder) => $builder
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('serial_number', 'like', '%'.$search.'%')))
                ->orderBy('title')
                ->limit(30)
                ->get(['id', 'title', 'serial_number'])
                ->map(fn (Products $product) => [
                    'id' => $product->id,
                    'text' => $product->title.' ('.$product->serial_number.')',
                ]),
        ]);
    }

    public function set(Request $request, PricingAdminService $pricingAdmin, AuditLogger $audit)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);
        $price = $pricingAdmin->setFixedPrice((int) $data['user_id'], (int) $data['product_id'], $data['price']);
        $audit->record('special_price.updated', $price, $data);

        return response()->json(['success' => true, 'message' => 'Ár elmentve.']);
    }

    public function delete(Request $request, PricingAdminService $pricingAdmin, AuditLogger $audit)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);
        $pricingAdmin->deleteFixedPrice((int) $data['user_id'], (int) $data['product_id']);
        $audit->record('special_price.deleted', null, $data);

        return response()->json(['success' => true, 'message' => 'Ár törölve.']);
    }

    public function saveMatrix(Request $request, AuditLogger $audit)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'extra_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fix_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);
        $data['extra_percent'] = $data['extra_percent'] ?? 0;
        $data['fix_price'] = $data['fix_price'] ?? 0;

        $this->pricingAdmin->saveMatrix($data);
        $audit->record('discount.matrix.updated', null, $data);

        return response()->json(['success' => true, 'message' => 'Árképzés elmentve.']);
    }

    public function globalCategory(Request $request, PricingAdminService $pricingAdmin, AuditLogger $audit)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
        $count = $pricingAdmin->applyCategoryToAllUsers((int) $data['category_id'], $data['discount_percent']);
        $audit->record('discount.category.global_updated', null, $data + ['user_count' => $count]);

        return response()->json([
            'success' => true,
            'message' => "Sikeresen beállítva {$data['discount_percent']}% kedvezmény {$count} ügyfélnél.",
        ]);
    }
}
