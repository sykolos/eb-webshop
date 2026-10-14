<?php

namespace App\Services;

use App\Models\Products;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class PricingService
{
    private const SCALE = 100;
    private const RATE_SCALE = 10000;
    private const VAT_RATE_BASIS_POINTS = 2700;
    private const CONTEXT_TTL_SECONDS = 300;

    public function contextForUser(?int $userId, iterable $products = []): PricingContext
    {
        $productIds = [];
        $categoryIds = [];

        foreach ($products as $product) {
            $productIds[] = (int) ($product->id ?? 0);
            $categoryIds[] = (int) ($product->category_id ?? 0);
        }

        $productIds = array_values(array_filter(array_unique($productIds)));
        $categoryIds = array_values(array_filter(array_unique($categoryIds)));

        if (!$userId) {
            return new PricingContext(null, [], [], 0);
        }

        $version = (int) Cache::get($this->versionKey($userId), 1);
        $cacheKey = 'pricing:context:'.$userId.':'.$version.':'.sha1(json_encode([
            $productIds,
            $categoryIds,
        ], JSON_THROW_ON_ERROR));

        return Cache::remember($cacheKey, self::CONTEXT_TTL_SECONDS, function () use ($userId, $productIds, $categoryIds, $version) {
            $discountQuery = DB::table('group_discounts')
                ->where('user_id', $userId);

            if ($categoryIds !== []) {
                $discountQuery->whereIn('category_id', $categoryIds);
            }

            $categoryDiscounts = $discountQuery->get()
                ->keyBy(fn ($row) => (string) $row->category_id)
                ->map(fn ($row) => [
                    'discount_percent' => (string) ($row->discount_percent ?? 0),
                    'extra_percent' => (string) ($row->extra_percent ?? 0),
                ])
                ->all();

            $specialQuery = DB::table('special_prices')
                ->where('user_id', $userId);

            if ($productIds !== []) {
                $specialQuery->whereIn('product_id', $productIds);
            }

            $specialPrices = $specialQuery->get()
                ->sortByDesc('id')
                ->unique('product_id')
                ->keyBy(fn ($row) => (string) $row->product_id)
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'price' => (string) ($row->price ?? 0),
                    'extra_percent' => (string) ($row->extra_percent ?? 0),
                ])
                ->all();

            return new PricingContext($userId, $categoryDiscounts, $specialPrices, $version);
        });
    }

    public function priceForUser(Products $product, ?int $userId = null, ?PricingContext $context = null): int|string
    {
        return $this->breakdown($product, $userId, $context)['final_price'];
    }

    public function breakdown(Products $product, ?int $userId = null, ?PricingContext $context = null): array
    {
        $context ??= $this->contextForUser($userId, [$product]);
        $discount = $context->categoryDiscount($product->category_id);
        $special = $context->specialPrice($product->id);
        $baseMinor = $this->toMinorUnits($product->price);
        $fixedMinor = $special ? $this->toMinorUnits($special['price'] ?? 0) : 0;

        $categoryPercent = (string) ($discount['discount_percent'] ?? 0);
        $extraPercent = (string) ($special['extra_percent'] ?? 0);
        $discountBasisPoints = $this->toRateBasisPoints($categoryPercent)
            + $this->toRateBasisPoints($extraPercent);
        $calculatedMinor = $this->applyDiscount($baseMinor, $discountBasisPoints);
        $finalMinor = $fixedMinor > 0 ? $fixedMinor : $calculatedMinor;

        return [
            'base_price' => $this->fromMinorUnits($baseMinor),
            'category_discount' => $this->numericPercent($categoryPercent),
            'extra_discount' => $this->numericPercent($extraPercent),
            'calculated_price' => $this->fromMinorUnits($calculatedMinor),
            'fixed_price' => $fixedMinor > 0 ? $this->fromMinorUnits($fixedMinor) : null,
            'final_price' => $this->fromMinorUnits($finalMinor),
            'discount_basis_points' => $discountBasisPoints,
        ];
    }

    public function quote(Products $product, int $packCount = 1, ?int $userId = null, ?PricingContext $context = null): array
    {
        $packCount = max(1, $packCount);
        $unitPrice = $this->breakdown($product, $userId, $context)['final_price'];
        $unitMinor = $this->toMinorUnits($unitPrice);
        $packQuantity = max(1, (int) ($product->product_unit?->quantity ?? 1));
        $lineNetMinor = $unitMinor * $packQuantity * $packCount;
        $vatMinor = $this->roundHalfUp($lineNetMinor * self::VAT_RATE_BASIS_POINTS, self::RATE_SCALE);

        return [
            'unit_net' => $unitPrice,
            'pack_quantity' => $packQuantity,
            'pack_count' => $packCount,
            'pack_net' => $this->fromMinorUnits($unitMinor * $packQuantity),
            'line_net' => $this->fromMinorUnits($lineNetMinor),
            'vat' => $this->fromMinorUnits($vatMinor),
            'line_gross' => $this->fromMinorUnits($lineNetMinor + $vatMinor),
            'vat_rate' => '27',
        ];
    }

    public function priceMany(iterable $products, ?int $userId = null): array
    {
        $products = is_array($products) ? $products : iterator_to_array($products);
        $context = $this->contextForUser($userId, $products);
        $prices = [];

        foreach ($products as $product) {
            $prices[(int) $product->id] = $this->breakdown($product, $userId, $context);
        }

        return $prices;
    }

    public function invalidateUser(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        $key = $this->versionKey($userId);
        Cache::add($key, 1, self::CONTEXT_TTL_SECONDS);
        Cache::increment($key);
    }

    public function invalidateUsers(iterable $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->invalidateUser((int) $userId);
        }
    }

    public function toMinorUnits(int|float|string|null $amount): int
    {
        $value = trim((string) ($amount ?? 0));
        if ($value === '') {
            return 0;
        }

        $negative = str_starts_with($value, '-');
        $value = ltrim($value, '+-');
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $whole = preg_replace('/[^0-9]/', '', $whole) ?: '0';
        $fraction = preg_replace('/[^0-9]/', '', $fraction) ?: '';
        $fraction = str_pad(substr($fraction, 0, 3), 3, '0');
        $minor = ((int) $whole * self::SCALE) + (int) substr($fraction, 0, 2);

        if ((int) ($fraction[2] ?? 0) >= 5) {
            $minor++;
        }

        return $negative ? -$minor : $minor;
    }

    private function versionKey(int $userId): string
    {
        return 'pricing:version:'.$userId;
    }

    private function toRateBasisPoints(int|float|string|null $percent): int
    {
        // A percentage with two decimals maps directly to basis points:
        // 10.00% -> 1000 / 10000.
        return $this->toMinorUnits($percent);
    }

    private function applyDiscount(int $amountMinor, int $discountBasisPoints): int
    {
        return $this->roundHalfUp(
            $amountMinor * (self::RATE_SCALE - $discountBasisPoints),
            self::RATE_SCALE
        );
    }

    private function roundHalfUp(int $numerator, int $denominator): int
    {
        if ($numerator < 0) {
            return -$this->roundHalfUp(-$numerator, $denominator);
        }

        return intdiv($numerator + intdiv($denominator, 2), $denominator);
    }

    public function fromMinorUnits(int $minor): int|string
    {
        if ($minor % self::SCALE === 0) {
            return intdiv($minor, self::SCALE);
        }

        return sprintf('%d.%02d', intdiv($minor, self::SCALE), abs($minor % self::SCALE));
    }

    private function numericPercent(string $percent): int|string
    {
        return $this->fromMinorUnits($this->toMinorUnits($percent));
    }
}
