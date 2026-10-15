<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Products;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CartService
{
    public function forUser(User|int $user): Cart
    {
        $userId = $user instanceof User ? $user->getKey() : (int) $user;
        $cart = $this->recordForUser($userId);

        if ($user instanceof User && session()->has('cart') && session('cart') !== []) {
            $this->migrateSession($user, (array) session('cart'));
            session()->forget('cart');
            $cart = $this->recordForUser($userId);
        }

        return $cart;
    }

    private function recordForUser(int $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId],
            ['last_activity_at' => now()]
        );
    }

    public function migrateSession(User $user, array $sessionCart): Cart
    {
        if ($sessionCart === []) {
            return $this->forUser($user);
        }

        $hash = hash('sha256', json_encode($sessionCart, JSON_THROW_ON_ERROR));

        $cart = DB::transaction(function () use ($user, $sessionCart, $hash) {
            $cart = $this->recordForUser($user->getKey());

            if ($cart->session_migration_hash === $hash) {
                return $cart;
            }

            foreach ($sessionCart as $item) {
                $productId = (int) data_get($item, 'product.id');
                $quantity = filter_var(data_get($item, 'quantity'), FILTER_VALIDATE_INT, [
                    'options' => ['min_range' => 1],
                ]);

                if ($productId && $quantity) {
                    $this->addLocked($cart, $productId, (int) $quantity);
                }
            }

            $cart->forceFill([
                'last_activity_at' => now(),
                'session_migration_hash' => $hash,
            ])->save();

            return $cart;
        });

        return $cart->load('items');
    }

    public function add(User $user, int $productId, int $packCount = 1): CartItem
    {
        if ($packCount < 1) {
            throw ValidationException::withMessages(['quantity' => 'A mennyiség legalább 1 legyen.']);
        }

        $product = Products::published()->find($productId);
        if (!$product) {
            abort(404);
        }

        if ($product->availability_status === 'out_of_stock') {
            throw ValidationException::withMessages(['cart' => 'Ez a termék jelenleg nem rendelhető.']);
        }

        return DB::transaction(function () use ($user, $productId, $packCount) {
            $cart = $this->forUser($user);
            Cart::query()->whereKey($cart->getKey())->lockForUpdate()->first();

            $item = $this->addLocked($cart, $productId, $packCount);
            $cart->forceFill(['last_activity_at' => now()])->save();

            return $item->load('product.product_unit');
        });
    }

    public function update(User $user, int $itemId, int $change): CartItem
    {
        if (!in_array($change, [-1, 1], true)) {
            throw ValidationException::withMessages(['change' => 'Csak egyesével módosítható a mennyiség.']);
        }

        return DB::transaction(function () use ($user, $itemId, $change) {
            $cart = $this->forUser($user);
            $item = $cart->items()->whereKey($itemId)->lockForUpdate()->firstOrFail();
            $item->quantity = max(1, $item->quantity + $change);
            $item->save();
            $cart->forceFill(['last_activity_at' => now()])->save();

            return $item->load('product.product_unit');
        });
    }

    public function remove(User $user, int $itemId): void
    {
        DB::transaction(function () use ($user, $itemId) {
            $cart = $this->forUser($user);
            $cart->items()->whereKey($itemId)->delete();
            $cart->forceFill(['last_activity_at' => now()])->save();
        });
    }

    public function clear(User $user): void
    {
        $cart = $this->forUser($user);
        $cart->items()->delete();
        $cart->forceFill(['last_activity_at' => now()])->save();
    }

    public function count(User $user): int
    {
        return (int) $this->forUser($user)->items()->sum('quantity');
    }

    public function lines(User $user): array
    {
        $cart = $this->forUser($user);
        $items = $cart->items()->with(['product.product_unit', 'product.media'])->get();

        return $items->map(function (CartItem $item) use ($user) {
            $product = $item->product;
            $available = $product
                && (bool) $product->is_published
                && !(bool) $product->is_archived
                && $product->availability_status !== 'out_of_stock';
            $quote = null;

            if ($available) {
                $quote = app(PricingService::class)->quote($product, $item->quantity, $user->id);
            }

            return [
                'item' => $item,
                'product' => $product,
                'quantity' => (int) $item->quantity,
                'pack_quantity' => max(1, (int) ($product?->product_unit?->quantity ?? 1)),
                'measure' => $product?->product_unit?->measure ?? 'db',
                'quote' => $quote,
                'available' => $available,
                'warning' => $available ? null : 'A termék már nem rendelhető ebben a formában.',
            ];
        })->all();
    }

    public function summary(User $user, ?array $lines = null): array
    {
        $lines ??= $this->lines($user);
        $pricing = app(PricingService::class);
        $netMinor = 0;
        $vatMinor = 0;

        foreach ($lines as $line) {
            if ($line['quote']) {
                $netMinor += $pricing->toMinorUnits($line['quote']['line_net']);
                $vatMinor += $pricing->toMinorUnits($line['quote']['vat']);
            }
        }

        return [
            'net' => $pricing->fromMinorUnits($netMinor),
            'vat' => $pricing->fromMinorUnits($vatMinor),
            'gross' => $pricing->fromMinorUnits($netMinor + $vatMinor),
            'has_unavailable' => collect($lines)->contains(fn ($line) => !$line['available']),
        ];
    }

    private function addLocked(Cart $cart, int $productId, int $quantity): CartItem
    {
        $item = $cart->items()->where('product_id', $productId)->lockForUpdate()->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return $item->fresh();
        }

        return $cart->items()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }
}
