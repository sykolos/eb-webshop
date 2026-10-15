<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Products;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class OfferService
{
    private const CACHE_VERSION_KEY = 'offers:version';

    public function __construct(private readonly AuditLogger $audit, private readonly CategoryTreeService $categories)
    {
    }

    public function active(): Collection
    {
        $version = (int) Cache::get(self::CACHE_VERSION_KEY, 1);
        return Cache::remember('offers:active:'.$version, 60, fn () => Offer::query()
            ->active()
            ->with(['products' => fn ($query) => $query->published()->with('product_unit'), 'categories', 'banners'])
            ->get());
    }

    public function productsFor(Offer $offer): Collection
    {
        $directIds = $offer->products->pluck('id')->map(fn ($id) => (int) $id)->all();
        $categoryIds = $offer->categories
            ->flatMap(fn ($category) => $this->categories->categoryAndDescendantIds((int) $category->id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($directIds === [] && $categoryIds === []) {
            return new Collection();
        }

        return Products::query()->published()->with('product_unit')
            ->where(function ($query) use ($directIds, $categoryIds): void {
                if ($directIds !== []) {
                    $query->whereIn('products.id', $directIds);
                }
                if ($categoryIds !== []) {
                    $query->orWhereIn('products.category_id', $categoryIds);
                }
            })
            ->orderBy('products.title')->get();
    }

    public function save(array $attributes, User $actor, ?Offer $offer = null): Offer
    {
        $wasExisting = $offer?->exists === true;
        $offer = DB::transaction(function () use ($attributes, $actor, $offer): Offer {
            $record = $offer ?: new Offer();
            $record->fill([
                'title' => trim($attributes['title']),
                'slug' => $this->uniqueSlug($attributes['slug'] ?? $attributes['title'], $record->exists ? $record : null),
                'summary' => filled($attributes['summary'] ?? null) ? trim($attributes['summary']) : null,
                'sort_order' => (int) ($attributes['sort_order'] ?? 0),
                'is_published' => (bool) ($attributes['is_published'] ?? false),
                'starts_at' => $attributes['starts_at'] ?? null,
                'ends_at' => $attributes['ends_at'] ?? null,
                'created_by' => $record->created_by ?: $actor->id,
            ]);
            $record->save();
            $record->products()->sync($this->pivotRows($attributes['product_ids'] ?? []));
            $record->categories()->sync($this->pivotRows($attributes['category_ids'] ?? []));
            return $record->refresh()->load(['products', 'categories', 'banners']);
        });

        $this->invalidate();
        $this->audit->record($wasExisting ? 'offer.updated' : 'offer.created', $offer, [
            'published' => $offer->is_published,
            'product_count' => $offer->products->count(),
            'category_count' => $offer->categories->count(),
        ]);
        return $offer;
    }

    public function setPublished(Offer $offer, bool $published, User $actor): Offer
    {
        $offer->update(['is_published' => $published]);
        $offer = $offer->refresh();
        $this->invalidate();
        $this->audit->record($published ? 'offer.published' : 'offer.archived', $offer, []);
        return $offer;
    }

    public function invalidate(): void
    {
        Cache::add(self::CACHE_VERSION_KEY, 1, now()->addDay());
        Cache::increment(self::CACHE_VERSION_KEY);
    }

    private function pivotRows(array $ids): array
    {
        return collect($ids)->mapWithKeys(fn ($id, $index) => [(int) $id => ['sort_order' => $index]])->all();
    }

    private function uniqueSlug(string $value, ?Offer $ignore = null): string
    {
        $base = Str::slug($value) ?: 'ajanlat';
        $slug = $base;
        $counter = 2;
        while (Offer::query()->where('slug', $slug)->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))->exists()) {
            $slug = $base.'-'.$counter++;
        }
        return $slug;
    }
}
