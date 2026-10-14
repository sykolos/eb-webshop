<?php

namespace App\Services;

final class PricingContext
{
    public function __construct(
        public readonly ?int $userId,
        public readonly array $categoryDiscounts,
        public readonly array $specialPrices,
        public readonly int $version,
    ) {
    }

    public function categoryDiscount(int|string|null $categoryId): array
    {
        return $this->categoryDiscounts[(string) $categoryId] ?? [
            'discount_percent' => '0',
            'extra_percent' => '0',
        ];
    }

    public function specialPrice(int|string|null $productId): ?array
    {
        return $this->specialPrices[(string) $productId] ?? null;
    }
}
