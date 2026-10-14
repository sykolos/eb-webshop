<?php

namespace App\Services;

use App\Models\FilterValue;
use App\Models\ProductMedia;
use App\Models\Products;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;

class ProductCatalogService
{
    public function query(array $filters = []): Builder
    {
        return Products::query()->with(['category', 'product_unit', 'media', 'filterValues.definition'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(fn ($part) => $part->where('title', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%"));
            })
            ->when($filters['category_id'] ?? null, fn (Builder $query, $id) => $query->whereIn('category_id', $this->categoryIds((int) $id)))
            ->when(isset($filters['availability_status']) && $filters['availability_status'] !== '', fn (Builder $query) => $query->where('availability_status', $filters['availability_status']))
            ->when(isset($filters['published']) && $filters['published'] !== '', fn (Builder $query) => $query->where('is_published', (bool) $filters['published']))
            ->when(!($filters['include_archived'] ?? false), fn (Builder $query) => $query->where('is_archived', false));
    }

    public function create(array $attributes, ?UploadedFile $image = null): Products
    {
        return DB::transaction(function () use ($attributes, $image): Products {
            $product = Products::create($this->attributes($attributes));
            if ($image) {
                $this->storeImage($product, $image);
            }
            $this->syncFilters($product, $attributes['filter_value_ids'] ?? []);

            return $product->load(['category', 'product_unit', 'media', 'filterValues']);
        });
    }

    public function update(Products $product, array $attributes, ?UploadedFile $image = null): Products
    {
        return DB::transaction(function () use ($product, $attributes, $image): Products {
            $product->update($this->attributes($attributes, $product));
            if ($image) {
                $this->storeImage($product, $image);
            }
            $this->syncFilters($product, $attributes['filter_value_ids'] ?? []);

            return $product->refresh()->load(['category', 'product_unit', 'media', 'filterValues']);
        });
    }

    public function archive(Products $product): void
    {
        $product->update(['is_archived' => true, 'is_published' => false]);
    }

    public function restore(Products $product): void
    {
        $product->update(['is_archived' => false]);
    }

    public function storeMedia(Products $product, UploadedFile $file, ?int $sortOrder = null): ProductMedia
    {
        if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
            $path = app(SecureFileStorage::class)->storePrivate($file, 'products/'.$product->id, ['pdf'], 10240);

            return $product->media()->create([
                'kind' => 'document', 'disk' => 'private', 'path' => $path,
                'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(), 'sort_order' => $sortOrder ?? 0, 'is_public' => false,
            ]);
        }

        return $this->storeImage($product, $file, true, $sortOrder);
    }

    public function updateMedia(ProductMedia $media, int $sortOrder, bool $isPublic): ProductMedia
    {
        $isPublic = $media->kind !== 'document' && $isPublic;
        $targetDisk = $isPublic ? 'public' : 'private';

        if ($media->disk !== $targetDisk && Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($targetDisk)->put($media->path, Storage::disk($media->disk)->get($media->path));
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->update([
            'disk' => $targetDisk,
            'sort_order' => max(0, $sortOrder),
            'is_public' => $isPublic,
        ]);

        return $media->refresh();
    }

    public function deleteMedia(ProductMedia $media): void
    {
        if ($media->disk === 'private') {
            app(SecureFileStorage::class)->deletePrivate($media->path);
        } elseif (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();
    }

    public function dataQualityReport(): array
    {
        return [
            'duplicate_serials' => Products::query()->select('serial_number', DB::raw('count(*) as total'))
                ->groupBy('serial_number')->having('total', '>', 1)->get(),
            'missing_category' => Products::whereNull('category_id')->orWhereDoesntHave('category')->count(),
            'missing_unit' => Products::whereNull('unit_id')->orWhereDoesntHave('product_unit')->count(),
            'missing_slug' => Products::whereNull('slug')->orWhere('slug', '')->count(),
            'archived' => Products::where('is_archived', true)->count(),
        ];
    }

    private function attributes(array $attributes, ?Products $product = null): array
    {
        $serial = trim((string) ($attributes['serialnum'] ?? $attributes['serial_number'] ?? ''));
        if ($serial === '') {
            throw ValidationException::withMessages(['serialnum' => 'A cikkszám kötelező.']);
        }

        $existing = Products::where('serial_number', $serial)
            ->when($product, fn ($query) => $query->where('id', '!=', $product->id))->exists();
        if ($existing) {
            throw ValidationException::withMessages(['serialnum' => 'Ez a cikkszám már használatban van.']);
        }

        return [
            'serial_number' => $serial,
            'title' => trim((string) $attributes['title']),
            'slug' => $this->uniqueSlug($attributes['slug'] ?? $attributes['title'], $product),
            'manufacturer' => filled($attributes['manufacturer'] ?? null) ? trim($attributes['manufacturer']) : null,
            'category_id' => (int) $attributes['category_id'],
            'unit_id' => (int) $attributes['unit_id'],
            'price' => $attributes['price'],
            'description' => $attributes['description'] ?? null,
            'specifications' => $attributes['specifications'] ?? $this->parseSpecifications($attributes['specifications_text'] ?? null),
            'is_published' => (bool) ($attributes['is_published'] ?? true),
            'availability_status' => $attributes['availability_status'] ?? 'in_stock',
        ];
    }

    private function storeImage(Products $product, UploadedFile $file, bool $asMedia = false, ?int $sortOrder = null): ProductMedia
    {
        $filename = 'products/'.Str::uuid().'.webp';
        Storage::disk('public')->put($filename, Image::read($file)->toWebp(90));

        $media = $product->media()->create([
            'kind' => 'image', 'disk' => 'public', 'path' => $filename,
            'original_name' => $file->getClientOriginalName(), 'mime_type' => 'image/webp',
            'size' => Storage::disk('public')->size($filename), 'is_public' => true,
            'sort_order' => $sortOrder ?? ((int) $product->media()->where('kind', 'image')->max('sort_order') + 1),
        ]);

        if (!$asMedia) {
            $old = $product->image;
            $product->update(['image' => $filename]);
            if ($old && $old !== $filename && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        return $media;
    }

    private function syncFilters(Products $product, array $valueIds): void
    {
        $category = \App\Models\Category::find($product->category_id);
        $allowedIds = collect(app(FilterCatalogService::class)->definitionsForCategory($category))
            ->flatMap(fn ($definition) => $definition->values->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->all();
        $ids = FilterValue::whereIn('id', array_map('intval', $valueIds))
            ->whereIn('id', $allowedIds)
            ->pluck('id')
            ->all();
        $product->filterValues()->sync($ids);
    }

    private function parseSpecifications(?string $text): ?array
    {
        if (!filled($text)) {
            return null;
        }

        $specifications = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode(':', $line, 2));
            if ($key !== '' && $value !== '') {
                $specifications[$key] = $value;
            }
        }

        return $specifications ?: null;
    }

    private function uniqueSlug(string $value, ?Products $ignore = null): string
    {
        $base = Str::slug($value) ?: 'termek';
        $slug = $base;
        $counter = 2;

        while (Products::where('slug', $slug)
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function categoryIds(int $categoryId): array
    {
        $service = app(CategoryTreeService::class);
        $category = \App\Models\Category::find($categoryId);
        return $category ? array_merge([$categoryId], $service->descendantIds($category)) : [$categoryId];
    }
}
