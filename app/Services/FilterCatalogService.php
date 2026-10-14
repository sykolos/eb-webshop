<?php

namespace App\Services;

use App\Models\Category;
use App\Models\FilterDefinition;
use App\Models\FilterValue;
use App\Models\Products;
use Illuminate\Support\Str;

class FilterCatalogService
{
    public function definitionsForCategory(?Category $category): array
    {
        if (!$category) {
            return [];
        }

        $ids = app(CategoryTreeService::class)->ancestorIds($category->id);

        return FilterDefinition::query()
            ->with(['values' => fn ($query) => $query->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('categories', fn ($query) => $query->whereIn('categories.id', $ids))
            ->orderBy('sort_order')->orderBy('name')->get()->all();
    }

    public function createDefinition(array $attributes): FilterDefinition
    {
        $definition = FilterDefinition::create([
            'name' => $attributes['name'],
            'slug' => $this->uniqueSlug($attributes['slug'] ?? $attributes['name']),
            'type' => $attributes['type'] ?? 'select',
            'is_active' => (bool) ($attributes['is_active'] ?? true),
            'sort_order' => (int) ($attributes['sort_order'] ?? 0),
        ]);

        $definition->categories()->sync($attributes['category_ids'] ?? []);

        foreach ($attributes['values'] ?? [] as $value) {
            if (filled($value)) {
                $this->createValue($definition, $value);
            }
        }

        return $definition->load(['values', 'categories']);
    }

    public function createValue(FilterDefinition $definition, string $label): FilterValue
    {
        return $definition->values()->create([
            'label' => $label,
            'slug' => $this->uniqueValueSlug($definition, $label),
            'is_active' => true,
        ]);
    }

    public function syncProductValues(Products $product, array $valueIds): void
    {
        $ids = FilterValue::whereIn('id', $valueIds)->pluck('id')->all();
        $product->filterValues()->sync($ids);
    }

    private function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'szuro';
        $slug = $base;
        $counter = 2;

        while (FilterDefinition::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function uniqueValueSlug(FilterDefinition $definition, string $value): string
    {
        $base = Str::slug($value) ?: 'ertek';
        $slug = $base;
        $counter = 2;

        while ($definition->values()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
