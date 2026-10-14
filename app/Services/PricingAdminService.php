<?php

namespace App\Services;

use App\Models\Products;
use App\Models\Special_prices;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class PricingAdminService
{
    public function __construct(
        private readonly PricingService $pricing,
    ) {
    }

    public function matrix(User $user, array $filters): LengthAwarePaginator
    {
        $query = Products::query()
            ->with('product_unit')
            ->orderBy('title');

        if (filled($filters['search'] ?? null)) {
            $search = trim((string) $filters['search']);
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('serial_number', 'like', '%'.$search.'%');
            });
        }

        if (filled($filters['category_id'] ?? null)) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['only_modified'])) {
            $query->where(function ($builder) use ($user) {
                $builder->whereExists(function ($subquery) use ($user) {
                    $subquery->selectRaw('1')
                        ->from('special_prices')
                        ->whereColumn('special_prices.product_id', 'products.id')
                        ->where('special_prices.user_id', $user->id);
                })->orWhereExists(function ($subquery) use ($user) {
                    $subquery->selectRaw('1')
                        ->from('group_discounts')
                        ->whereColumn('group_discounts.category_id', 'products.category_id')
                        ->where('group_discounts.user_id', $user->id)
                        ->where(function ($discount) {
                            $discount->where('discount_percent', '>', 0)
                                ->orWhere('extra_percent', '>', 0);
                        });
                });
            });
        }

        $products = $query->paginate(20)->onEachSide(0);
        $context = $this->pricing->contextForUser($user->id, $products->getCollection());

        $products->getCollection()->transform(function (Products $product) use ($user, $context) {
            $breakdown = $this->pricing->breakdown($product, $user->id, $context);
            $product->setAttribute('pricing', $breakdown);
            $product->setAttribute('category_discount', [
                'discount_percent' => $breakdown['category_discount'],
            ]);
            $product->setAttribute('special_price', $context->specialPrice($product->id));

            return $product;
        });

        return $products;
    }

    public function saveMatrix(array $data): void
    {
        DB::transaction(function () use ($data) {
            $now = now();

            DB::table('group_discounts')->updateOrInsert(
                [
                    'user_id' => $data['user_id'],
                    'category_id' => $data['category_id'],
                ],
                [
                    'discount_percent' => $data['discount_percent'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $hasSpecialRule = $this->pricing->toMinorUnits($data['fix_price'] ?? 0) > 0
                || $this->pricing->toMinorUnits($data['extra_percent'] ?? 0) !== 0;

            $specialQuery = Special_prices::query()
                ->where('user_id', $data['user_id'])
                ->where('product_id', $data['product_id']);

            if ($hasSpecialRule) {
                $special = $specialQuery->latest('id')->first() ?? new Special_prices();
                $special->fill([
                    'user_id' => $data['user_id'],
                    'product_id' => $data['product_id'],
                    'price' => $data['fix_price'] ?: 0,
                    'extra_percent' => $data['extra_percent'] ?? 0,
                ]);
                $special->save();
            } else {
                $specialQuery->delete();
            }

            $this->pricing->invalidateUser((int) $data['user_id']);
        });
    }

    public function setFixedPrice(int $userId, int $productId, int|string $price): Special_prices
    {
        $special = Special_prices::query()
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->latest('id')
            ->first() ?? new Special_prices();
        $special->fill([
            'user_id' => $userId,
            'product_id' => $productId,
            'price' => $price,
            'extra_percent' => $special->extra_percent ?? 0,
        ]);
        $special->save();
        $this->pricing->invalidateUser($userId);

        return $special;
    }

    public function deleteFixedPrice(int $userId, int $productId): void
    {
        Special_prices::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
        $this->pricing->invalidateUser($userId);
    }

    public function applyCategoryToAllUsers(int $categoryId, int|string $percent): int
    {
        $users = User::query()->pluck('id');
        $now = now();
        $rows = $users->map(fn ($userId) => [
            'user_id' => $userId,
            'category_id' => $categoryId,
            'discount_percent' => $percent,
            'extra_percent' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows !== []) {
            DB::table('group_discounts')->upsert(
                $rows,
                ['user_id', 'category_id'],
                ['discount_percent', 'updated_at']
            );
            $this->pricing->invalidateUsers($users);
        }

        return count($rows);
    }
}
