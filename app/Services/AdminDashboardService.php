<?php

namespace App\Services;

use App\Models\ActivityEvent;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Products;
use App\Models\RecommendedProduct;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class AdminDashboardService
{
    public static function invalidateSummaryCache(): void
    {
        Cache::add('admin-dashboard:version', 1, now()->addDay());
        Cache::increment('admin-dashboard:version');
    }

    public function summary(Carbon $from, Carbon $to): array
    {
        $version = (int) Cache::get('admin-dashboard:version', 1);
        $key = 'admin-dashboard:'.$version.':'.sha1($from->toDateString().'|'.$to->toDateString());
        return Cache::remember($key, 60, function () use ($from, $to): array {
            $orders = $this->periodOrders($from, $to);
            $total = (float) $orders->sum(DB::raw('COALESCE(NULLIF(subtotal_net, 0), total)'));
            $count = (clone $orders)->count();
            return [
                'net_total' => $total,
                'order_count' => $count,
                'average_order' => $count ? $total / $count : 0,
                'open_orders' => Order::query()->whereNull('deleted_at')->whereNotIn('status', ['kiszállítva', 'törölve'])->count(),
                'pending_partners' => User::query()->where('account_status', 'pending')->count(),
                'incomplete_products' => Products::query()->where(function ($q) { $q->whereNull('title')->orWhereNull('serial_number')->orWhereNull('category_id'); })->count(),
                'processing_orders' => (clone $orders)->whereIn('status', ['függőben', 'feldolgozás', 'pending'])->count(),
                'availability' => Products::query()->select('availability_status', DB::raw('count(*) as aggregate'))->groupBy('availability_status')->pluck('aggregate', 'availability_status')->all(),
                'active_visitors' => ActivityEvent::query()->where('occurred_at', '>=', now()->subMinutes(15))->distinct('session_hash')->count('session_hash'),
            ];
        });
    }

    public function topProducts(Carbon $from, Carbon $to, int $limit = 8)
    {
        return DB::table('items')
            ->join('orders', 'orders.id', '=', 'items.order_id')
            ->leftJoin('products', 'products.id', '=', 'items.product_id')
            ->whereNull('orders.deleted_at')
            ->whereNotIn('orders.status', ['törölve'])
            ->whereBetween('orders.created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])
            ->select('items.product_id', 'items.product_title', 'items.serial_number', 'products.title', 'products.serial_number as product_serial_number',
                DB::raw('SUM(COALESCE(items.line_net, items.unit_price * items.quantity)) as net_sales'),
                DB::raw('SUM(items.quantity) as quantity'))
            ->groupBy('items.product_id', 'items.product_title', 'items.serial_number', 'products.title', 'products.serial_number')
            ->orderByDesc('net_sales')->limit($limit)->get();
    }

    public function featuredProducts()
    {
        return RecommendedProduct::with('product')->orderBy('sort_order')->orderBy('id')->get();
    }

    public function carts(Request $request): LengthAwarePaginator
    {
        return Cart::query()->with(['user.user_invoice', 'items.product.product_unit'])
            ->withCount('items')->whereHas('items')
            ->when($request->filled('state'), fn ($q) => $this->applyCartState($q, $request->string('state')->toString()))
            ->latest('last_activity_at')->paginate(25)->withQueryString();
    }

    public function cartRows(LengthAwarePaginator $carts): array
    {
        $pricing = app(PricingService::class);
        return collect($carts->items())->map(function (Cart $cart) use ($pricing): array {
            $total = 0;
            foreach ($cart->items as $item) {
                if ($item->product) {
                    $quote = $pricing->quote($item->product, (int) $item->quantity, $cart->user_id);
                    $total += (float) $quote['line_net'];
                }
            }
            return ['cart' => $cart, 'total' => $total, 'state' => $this->cartState($cart)];
        })->all();
    }

    public function users(Carbon $cutoff): LengthAwarePaginator
    {
        return User::query()->where('is_admin', false)->withMax(['orders' => fn ($q) => $q->whereNotIn('status', ['törölve'])], 'created_at')
            ->where(function ($q) use ($cutoff) { $q->whereDoesntHave('orders')->orWhereHas('orders', fn ($o) => $o->whereNotIn('status', ['törölve'])->where('created_at', '<', $cutoff)); })
            ->orderByRaw('last_seen_at IS NULL DESC')->orderBy('last_seen_at')->paginate(25)->withQueryString();
    }

    public function activity(Request $request): LengthAwarePaginator
    {
        return ActivityEvent::query()->with('user')->when($request->filled('event_name'), fn ($q) => $q->where('event_name', $request->string('event_name')))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('occurred_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('occurred_at', '<=', $request->date('to')))
            ->latest('occurred_at')->paginate(40)->withQueryString();
    }

    public function cartState(Cart $cart): string
    {
        if ($cart->converted_at) return 'converted';
        return $cart->last_activity_at && $cart->last_activity_at->gte(now()->subDays(7)) ? 'active' : 'abandoned';
    }

    private function periodOrders(Carbon $from, Carbon $to)
    {
        return Order::query()->whereNull('deleted_at')->whereNotIn('status', ['törölve'])->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    private function applyCartState($query, string $state)
    {
        if ($state === 'converted') return $query->whereNotNull('converted_at');
        if ($state === 'abandoned') return $query->whereNull('converted_at')->where('last_activity_at', '<', now()->subDays(7));
        return $query->whereNull('converted_at')->where('last_activity_at', '>=', now()->subDays(7));
    }
}
