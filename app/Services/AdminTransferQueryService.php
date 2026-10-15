<?php

namespace App\Services;

use App\Models\Category;
use App\Models\FilterDefinition;
use App\Models\Item;
use App\Models\Order;
use App\Models\Products;
use App\Models\RecommendedProduct;
use App\Models\Special_prices;
use App\Models\User;
use App\Models\product_unit;
use Illuminate\Support\LazyCollection;

final class AdminTransferQueryService
{
    public function headers(string $dataset): array
    {
        return match ($dataset) {
            'orders' => ['id' => 'ID', 'order_number' => 'Rendelésszám', 'partner' => 'Partner', 'email' => 'Email', 'status' => 'Státusz', 'total' => 'Nettó összeg', 'created_at' => 'Létrehozva'],
            'order_items' => ['order_number' => 'Rendelésszám', 'sku' => 'Cikkszám', 'product' => 'Termék', 'quantity' => 'Mennyiség', 'unit_price' => 'Egységár', 'created_at' => 'Létrehozva'],
            'products' => ['serial_number' => 'Cikkszám', 'title' => 'Terméknév', 'category' => 'Kategória', 'price' => 'Nettó egységár', 'unit' => 'Egység', 'manufacturer' => 'Gyártó', 'availability_status' => 'Elérhetőség', 'is_published' => 'Publikus', 'is_archived' => 'Archivált'],
            'categories' => ['id' => 'ID', 'path' => 'Kategóriaútvonal', 'name' => 'Név', 'parent_id' => 'Szülő ID', 'is_active' => 'Aktív', 'sort_order' => 'Sorrend'],
            'filters' => ['definition' => 'Szűrő', 'type' => 'Típus', 'value' => 'Érték', 'is_active' => 'Aktív', 'categories' => 'Kategóriák'],
            'partners' => ['id' => 'ID', 'name' => 'Név', 'company' => 'Cég', 'email' => 'Email', 'role' => 'Szerepkör', 'account_status' => 'Fiókállapot', 'created_at' => 'Létrehozva', 'last_login_at' => 'Utolsó belépés'],
            'units' => ['id' => 'ID', 'unit' => 'Egység', 'quantity' => 'Szorzó', 'measure' => 'Mérték', 'is_active' => 'Aktív'],
            'special_prices' => ['id' => 'ID', 'partner' => 'Partner', 'sku' => 'Cikkszám', 'product' => 'Termék', 'price' => 'Speciális ár', 'created_at' => 'Létrehozva'],
            'featured_products' => ['sort_order' => 'Sorrend', 'sku' => 'Cikkszám', 'product' => 'Termék'],
            default => throw new \InvalidArgumentException('Ismeretlen export adatkör: '.$dataset),
        };
    }

    public function rows(string $dataset, array $filters = []): LazyCollection
    {
        return match ($dataset) {
            'orders' => $this->orders($filters),
            'order_items' => $this->orderItems($filters),
            'products' => $this->products($filters),
            'categories' => $this->categories($filters),
            'filters' => $this->filters($filters),
            'partners' => $this->partners($filters),
            'units' => $this->units($filters),
            'special_prices' => $this->specialPrices($filters),
            'featured_products' => $this->featuredProducts($filters),
            default => throw new \InvalidArgumentException('Ismeretlen export adatkör: '.$dataset),
        };
    }

    private function orders(array $filters): LazyCollection
    {
        $query = Order::query()->with('user')->when($filters['include_deleted'] ?? false, fn ($q) => $q->withTrashed());
        $query->when(filled($filters['search'] ?? null), function ($q) use ($filters): void {
            $value = trim($filters['search']);
            $q->where(function ($nested) use ($value): void {
                $nested->where('order_number', 'like', '%'.$value.'%')->orWhere('email', 'like', '%'.$value.'%')->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%'.$value.'%'));
            });
        });
        $query->when(filled($filters['status'] ?? null), fn ($q) => $q->where('status', $filters['status']))
            ->when(filled($filters['from_date'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['to_date']));

        return LazyCollection::make(function () use ($query): \Generator {
            foreach ($query->orderBy('id')->cursor() as $order) {
                yield [$order->id, $order->displayNumber(), $order->user?->name ?: $order->name, $order->email, $order->status, $order->total, $order->created_at?->toDateTimeString()];
            }
        });
    }

    private function orderItems(array $filters): LazyCollection
    {
        $query = Item::query()->with(['order', 'product'])
            ->when(filled($filters['search'] ?? null), fn ($q) => $q->whereHas('product', fn ($p) => $p->where('serial_number', 'like', '%'.$filters['search'].'%')->orWhere('title', 'like', '%'.$filters['search'].'%')))
            ->when(filled($filters['from_date'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['to_date']));

        return LazyCollection::make(function () use ($query): \Generator {
            foreach ($query->orderBy('id')->cursor() as $item) {
                yield [$item->order?->displayNumber(), $item->product?->serial_number, $item->product?->title, $item->quantity, $item->unit_price, $item->created_at?->toDateTimeString()];
            }
        });
    }

    private function products(array $filters): LazyCollection
    {
        $query = app(ProductCatalogService::class)->query($filters)->orderBy('products.id');

        return LazyCollection::make(function () use ($query): \Generator {
            foreach ($query->cursor() as $product) {
                yield [$product->serial_number, $product->title, $product->category?->name, $product->price, $product->product_unit?->unit, $product->manufacturer, Products::AVAILABILITY_STATUSES[$product->availability_status] ?? $product->availability_status, $product->is_published ? 'Igen' : 'Nem', $product->is_archived ? 'Igen' : 'Nem'];
            }
        });
    }

    private function categories(array $filters): LazyCollection
    {
        $categories = Category::query()->orderBy('parent_id')->orderBy('sort_order')->orderBy('name')->get();
        $paths = [];
        return LazyCollection::make(function () use ($categories, &$paths): \Generator {
            foreach ($categories as $category) {
                $paths[$category->id] = $this->categoryPath($category, $paths);
                yield [$category->id, $paths[$category->id], $category->name, $category->parent_id, $category->is_active ? 'Igen' : 'Nem', $category->sort_order];
            }
        });
    }

    private function filters(array $filters): LazyCollection
    {
        $definitions = FilterDefinition::with(['values', 'categories'])->orderBy('sort_order')->orderBy('name')->get();
        return LazyCollection::make(function () use ($definitions): \Generator {
            foreach ($definitions as $definition) {
                $categories = $definition->categories->pluck('name')->implode(' | ');
                if ($definition->values->isEmpty()) {
                    yield [$definition->name, $definition->type, '', $definition->is_active ? 'Igen' : 'Nem', $categories];
                    continue;
                }
                foreach ($definition->values as $value) {
                    yield [$definition->name, $definition->type, $value->label, $value->is_active ? 'Igen' : 'Nem', $categories];
                }
            }
        });
    }

    private function partners(array $filters): LazyCollection
    {
        $query = User::query()->with('user_invoice')->when($filters['include_archived'] ?? false, fn ($q) => $q->withTrashed());
        $query->when(filled($filters['search'] ?? null), function ($q) use ($filters): void {
            $value = trim($filters['search']);
            $q->where(fn ($nested) => $nested->where('name', 'like', '%'.$value.'%')->orWhere('email', 'like', '%'.$value.'%')->orWhereHas('user_invoice', fn ($i) => $i->where('company_name', 'like', '%'.$value.'%')));
        });
        $query->when(filled($filters['status'] ?? null), fn ($q) => $q->where('account_status', $filters['status']))
            ->when(filled($filters['role'] ?? null), fn ($q) => $q->where('role', $filters['role']))
            ->when(filled($filters['from_date'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['from_date']))
            ->when(filled($filters['to_date'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['to_date']));

        return LazyCollection::make(function () use ($query): \Generator {
            foreach ($query->orderBy('id')->cursor() as $user) {
                yield [$user->id, $user->name, $user->user_invoice?->company_name, $user->email, $user->role ?: ($user->is_admin ? 'administrator' : 'partner'), $user->account_status, $user->created_at?->toDateTimeString(), $user->last_login_at?->toDateTimeString()];
            }
        });
    }

    private function units(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (product_unit::query()->orderBy('unit')->cursor() as $unit) {
                yield [$unit->id, $unit->unit, $unit->quantity, $unit->measure, $unit->is_active ? 'Igen' : 'Nem'];
            }
        });
    }

    private function specialPrices(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (Special_prices::with(['user', 'product'])->orderBy('id')->cursor() as $price) {
                yield [$price->id, $price->user?->name, $price->product?->serial_number, $price->product?->title, $price->price, $price->created_at?->toDateTimeString()];
            }
        });
    }

    private function featuredProducts(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (RecommendedProduct::with('product')->orderBy('sort_order')->orderBy('id')->cursor() as $featured) {
                yield [$featured->sort_order, $featured->product?->serial_number, $featured->product?->title];
            }
        });
    }

    private function categoryPath(Category $category, array &$paths): string
    {
        if (isset($paths[$category->id])) {
            return $paths[$category->id];
        }

        $names = [$category->name];
        $parent = $category->parent_id ? Category::find($category->parent_id) : null;
        $guard = 0;
        while ($parent && $guard++ < 100) {
            array_unshift($names, $parent->name);
            $parent = $parent->parent_id ? Category::find($parent->parent_id) : null;
        }

        return $paths[$category->id] = implode(' > ', $names);
    }
}
