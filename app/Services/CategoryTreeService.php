<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryTreeService
{
    public function roots(bool $activeOnly = false): EloquentCollection
    {
        $query = Category::query()->with(['children' => function ($children) use ($activeOnly): void {
            if ($activeOnly) {
                $children->active();
            }
        }])->orderBy('sort_order')->orderBy('name');

        if ($activeOnly) {
            $query->active();
        }

        return $query->whereNull('parent_id')->get();
    }

    public function allForSelect(bool $activeOnly = false): EloquentCollection
    {
        $query = Category::query()->orderBy('sort_order')->orderBy('name');

        if ($activeOnly) {
            $query->active();
        }

        return $query->get();
    }

    public function create(array $attributes): Category
    {
        $parentId = $attributes['parent_id'] ?? null;

        return Category::create([
            'name' => $attributes['name'],
            'slug' => $this->uniqueSlug($attributes['slug'] ?? $attributes['name']),
            'parent_id' => $parentId ?: null,
            'sort_order' => (int) ($attributes['sort_order'] ?? 0),
            'is_active' => (bool) ($attributes['is_active'] ?? true),
        ]);
    }

    public function update(Category $category, array $attributes): Category
    {
        $parentId = $attributes['parent_id'] ?? null;
        $this->assertParentAllowed($category, $parentId ? (int) $parentId : null);

        $category->update([
            'name' => $attributes['name'],
            'slug' => $this->uniqueSlug($attributes['slug'] ?? $attributes['name'], $category),
            'parent_id' => $parentId ?: null,
            'sort_order' => (int) ($attributes['sort_order'] ?? 0),
            'is_active' => (bool) ($attributes['is_active'] ?? false),
        ]);

        return $category->refresh();
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw ValidationException::withMessages(['category' => 'Gyermek kategóriát tartalmazó kategória nem törölhető.']);
        }

        if ($category->products()->exists()) {
            throw ValidationException::withMessages(['category' => 'Terméket tartalmazó kategória nem törölhető.']);
        }

        $category->delete();
    }

    public function assertParentAllowed(Category $category, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === $category->id) {
            throw ValidationException::withMessages(['parent_id' => 'A kategória nem lehet saját maga szülője.']);
        }

        $descendantIds = $this->descendantIds($category);
        if (in_array($parentId, $descendantIds, true)) {
            throw ValidationException::withMessages(['parent_id' => 'A kategória nem helyezhető a saját részfája alá.']);
        }

        if (!Category::whereKey($parentId)->exists()) {
            throw ValidationException::withMessages(['parent_id' => 'A kiválasztott szülő kategória nem létezik.']);
        }
    }

    public function descendantIds(Category $category): array
    {
        $ids = [];
        $pending = [$category->id];

        while ($pending) {
            $children = Category::whereIn('parent_id', $pending)->pluck('id')->all();
            $ids = array_merge($ids, $children);
            $pending = $children;
        }

        return array_map('intval', $ids);
    }

    public function categoryAndDescendantIds(?int $categoryId): array
    {
        if (!$categoryId || !($category = Category::find($categoryId))) {
            return $categoryId ? [$categoryId] : [];
        }

        return array_merge([$category->id], $this->descendantIds($category));
    }

    public function ancestorIds(?int $categoryId): array
    {
        $ids = [];
        $seen = [];

        while ($categoryId && !isset($seen[$categoryId])) {
            $seen[$categoryId] = true;
            $category = Category::find($categoryId);
            if (!$category) {
                break;
            }
            $ids[] = $category->id;
            $categoryId = $category->parent_id;
        }

        return $ids;
    }

    public function flatten(EloquentCollection|Collection $categories, int $level = 0): Collection
    {
        $flat = collect();
        foreach ($categories as $category) {
            $category->tree_level = $level;
            $flat->push($category);
            $flat = $flat->merge($this->flatten($category->children, $level + 1));
        }

        return $flat;
    }

    private function uniqueSlug(string $value, ?Category $ignore = null): string
    {
        $base = Str::slug($value) ?: 'kategori';
        $slug = $base;
        $counter = 2;

        while (Category::where('slug', $slug)
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
