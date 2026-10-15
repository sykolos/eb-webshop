<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\User_shipping;
use Illuminate\Support\Facades\DB;

final class AccountService
{
    public function __construct(private readonly CartService $carts)
    {
    }

    public function dashboard(User $user): array
    {
        $this->ensureDefaultAddress($user);
        $user->loadMissing(['user_invoice', 'user_shipping', 'preference']);

        return [
            'recentOrders' => $user->orders()->latest()->limit(5)->get(),
            'favoriteProducts' => $user->favoriteProducts()->published()->with('product_unit')->get(),
            'preferences' => $user->preference ?: $this->preferences($user),
        ];
    }

    public function preferences(User $user): UserPreference
    {
        return UserPreference::firstOrCreate(['user_id' => $user->id]);
    }

    public function updatePreferences(User $user, array $data): UserPreference
    {
        return $this->preferences($user)->update($data)
            ? $this->preferences($user)->fresh()
            : $this->preferences($user);
    }

    public function setDefaultAddress(User $user, User_shipping $address): void
    {
        abort_unless((int) $address->user_id === (int) $user->id, 403);

        DB::transaction(function () use ($user, $address) {
            User_shipping::where('user_id', $user->id)->update(['is_default' => false]);
            $address->forceFill(['is_default' => true])->save();
        });
    }

    public function createAddress(User $user, array $data): User_shipping
    {
        return DB::transaction(function () use ($user, $data) {
            $data['is_default'] = !User_shipping::where('user_id', $user->id)->exists();
            return $user->user_shipping()->create($data);
        });
    }

    public function ensureDefaultAddress(User $user): void
    {
        $addresses = $user->user_shipping()->orderBy('id')->get();
        if ($addresses->isNotEmpty() && !$addresses->contains('is_default', true)) {
            $addresses->first()->forceFill(['is_default' => true])->save();
        }
    }

    public function deleteAddress(User $user, User_shipping $address): void
    {
        abort_unless((int) $address->user_id === (int) $user->id, 403);

        DB::transaction(function () use ($user, $address) {
            $wasDefault = (bool) $address->is_default;
            $address->delete();
            if ($wasDefault) {
                $user->user_shipping()->latest('id')->first()?->forceFill(['is_default' => true])->save();
            }
        });
    }

    public function reorder(User $user, Order $order): array
    {
        abort_unless((int) $order->user_id === (int) $user->id, 403);

        $added = [];
        $skipped = [];
        foreach ($order->items()->with('product.product_unit')->get() as $item) {
            $product = $item->product;
            if (!$product || !$product->is_published || $product->is_archived || $product->availability_status === 'out_of_stock') {
                $skipped[] = $item->product_title ?: 'Történeti termék';
                continue;
            }

            $packQuantity = max(1, (int) ($product->product_unit?->quantity ?? 1));
            $packCount = max(1, (int) ceil(((int) $item->quantity) / $packQuantity));
            $this->carts->add($user, $product->id, $packCount);
            $added[] = $product->title;
        }

        return compact('added', 'skipped');
    }

    public function toggleFavorite(User $user, Products $product): bool
    {
        $favorite = $user->favoriteProducts()->whereKey($product->id)->first();
        if ($favorite) {
            $user->favoriteProducts()->detach($product->id);
            return false;
        }

        $user->favoriteProducts()->attach($product->id);
        return true;
    }
}
