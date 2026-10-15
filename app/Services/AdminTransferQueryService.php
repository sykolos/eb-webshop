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
use App\Models\Banner;
use App\Models\Offer;
use App\Models\ActivityEvent;
use App\Models\Cart;
use App\Models\EmailCampaign;
use App\Models\EmailDelivery;
use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\Ticket;
use App\Models\TicketMessage;
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
            'partners' => ['id' => 'ID', 'name' => 'Név', 'company' => 'Cég', 'email' => 'Email', 'role' => 'Szerepkör', 'account_status' => 'Fiókállapot', 'created_at' => 'Létrehozva', 'last_login_at' => 'Utolsó belépés', 'last_seen_at' => 'Utolsó látogatás'],
            'units' => ['id' => 'ID', 'unit' => 'Egység', 'quantity' => 'Szorzó', 'measure' => 'Mérték', 'is_active' => 'Aktív'],
            'special_prices' => ['id' => 'ID', 'partner' => 'Partner', 'sku' => 'Cikkszám', 'product' => 'Termék', 'price' => 'Speciális ár', 'created_at' => 'Létrehozva'],
            'featured_products' => ['sort_order' => 'Sorrend', 'sku' => 'Cikkszám', 'product' => 'Termék'],
            'banners' => ['id' => 'ID', 'slot' => 'Hely', 'title' => 'Cím', 'alt_text' => 'Alt szöveg', 'link_url' => 'Link', 'offer' => 'Ajánlat', 'sort_order' => 'Sorrend', 'is_published' => 'Publikus', 'starts_at' => 'Kezdet', 'ends_at' => 'Vége'],
            'offers' => ['id' => 'ID', 'title' => 'Ajánlat', 'slug' => 'Slug', 'products' => 'Termékek', 'categories' => 'Kategóriák', 'is_published' => 'Publikus', 'starts_at' => 'Kezdet', 'ends_at' => 'Vége'],
            'carts' => ['id' => 'ID', 'partner' => 'Partner', 'email' => 'Email', 'last_activity_at' => 'Utolsó aktivitás', 'item_count' => 'Tételszám', 'state' => 'Állapot', 'current_net' => 'Aktuális nettó'],
            'activity_events' => ['id' => 'ID', 'event_name' => 'Esemény', 'page_path' => 'Útvonal', 'device_type' => 'Eszköz', 'campaign_source' => 'Forrás', 'occurred_at' => 'Időpont', 'payload' => 'Maszkolt adat'],
            'email_campaigns' => ['id' => 'ID', 'name' => 'Kampány', 'subject' => 'Tárgy', 'type' => 'Típus', 'status' => 'Állapot', 'segment' => 'Szegmens', 'recipient_count' => 'Címzettek', 'sent_count' => 'Elküldve', 'failed_count' => 'Hibás', 'skipped_count' => 'Kihagyva', 'scheduled_at' => 'Ütemezés', 'created_at' => 'Létrehozva'],
            'email_deliveries' => ['id' => 'ID', 'campaign_id' => 'Kampány ID', 'email' => 'Email', 'type' => 'Típus', 'status' => 'Állapot', 'attempts' => 'Próbálkozás', 'last_error' => 'Hiba', 'sent_at' => 'Elküldve', 'created_at' => 'Létrehozva'],
            'automation_rules' => ['id' => 'ID', 'key' => 'Kulcs', 'name' => 'Név', 'event' => 'Esemény', 'enabled' => 'Aktív', 'delay_minutes' => 'Késleltetés perc', 'timezone' => 'Időzóna', 'max_per_user' => 'Maximum/felhasználó', 'cooldown_minutes' => 'Cooldown perc', 'next_run_at' => 'Következő futás'],
            'automation_runs' => ['id' => 'ID', 'rule' => 'Szabály', 'event' => 'Esemény', 'email' => 'Email', 'status' => 'Állapot', 'attempts' => 'Próbálkozás', 'scheduled_for' => 'Ütemezve', 'executed_at' => 'Végrehajtva', 'last_error' => 'Hiba', 'created_at' => 'Létrehozva'],
            'tickets' => ['id' => 'ID', 'ticket_number' => 'Ticket', 'partner' => 'Partner', 'email' => 'Email', 'order_number' => 'Rendelés', 'type' => 'Típus', 'priority' => 'Prioritás', 'status' => 'Állapot', 'assignee' => 'Felelős', 'response_due_at' => 'Válasz határideje', 'resolution_due_at' => 'Megoldási határidő', 'last_reply_at' => 'Utolsó válasz', 'created_at' => 'Létrehozva'],
            'ticket_messages' => ['id' => 'ID', 'ticket_number' => 'Ticket', 'author' => 'Szerző', 'email' => 'Email', 'is_internal' => 'Belső', 'body_length' => 'Üzenethossz', 'attachment_count' => 'Csatolmányok', 'created_at' => 'Létrehozva'],
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
            'banners' => $this->banners($filters),
            'offers' => $this->offers($filters),
            'carts' => $this->carts($filters),
            'activity_events' => $this->activityEvents($filters),
            'email_campaigns' => $this->emailCampaigns(),
            'email_deliveries' => $this->emailDeliveries(),
            'automation_rules' => $this->automationRules(),
            'automation_runs' => $this->automationRuns(),
            'tickets' => $this->tickets(),
            'ticket_messages' => $this->ticketMessages(),
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
                yield [$user->id, $user->name, $user->user_invoice?->company_name, $user->email, $user->role ?: ($user->is_admin ? 'administrator' : 'partner'), $user->account_status, $user->created_at?->toDateTimeString(), $user->last_login_at?->toDateTimeString(), $user->last_seen_at?->toDateTimeString()];
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

    private function banners(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (Banner::query()->with('offer')->orderBy('slot')->orderBy('sort_order')->orderBy('id')->cursor() as $banner) {
                yield [$banner->id, $banner->slot, $banner->title, $banner->alt_text, $banner->link_url, $banner->offer?->title, $banner->sort_order, $banner->is_published ? 'Igen' : 'Nem', $banner->starts_at?->toDateTimeString(), $banner->ends_at?->toDateTimeString()];
            }
        });
    }

    private function offers(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (Offer::query()->withCount(['products', 'categories'])->orderBy('sort_order')->orderBy('id')->cursor() as $offer) {
                yield [$offer->id, $offer->title, $offer->slug, $offer->products_count, $offer->categories_count, $offer->is_published ? 'Igen' : 'Nem', $offer->starts_at?->toDateTimeString(), $offer->ends_at?->toDateTimeString()];
            }
        });
    }

    private function carts(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            $pricing = app(PricingService::class);
            foreach (Cart::query()->with('user')->whereHas('items')->orderBy('id')->cursor() as $cart) {
                $total = 0;
                $items = $cart->items()->with('product.product_unit')->get();
                foreach ($items as $item) {
                    if ($item->product) {
                        $total += (float) $pricing->quote($item->product, (int) $item->quantity, $cart->user_id)['line_net'];
                    }
                }
                $state = $cart->converted_at ? 'Rendeléssé vált' : ($cart->last_activity_at?->gte(now()->subDays(7)) ? 'Aktív' : 'Elhagyott');
                yield [$cart->id, $cart->user?->name, $cart->user?->email, $cart->last_activity_at?->toDateTimeString(), $items->count(), $state, $total];
            }
        });
    }

    private function activityEvents(array $filters): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (ActivityEvent::query()->orderBy('id')->cursor() as $event) {
                yield [$event->id, $event->event_name, $event->page_path, $event->device_type, $event->campaign_source, $event->occurred_at?->toDateTimeString(), json_encode($event->payload ?? [], JSON_UNESCAPED_UNICODE)];
            }
        });
    }

    private function emailCampaigns(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (EmailCampaign::query()->orderBy('id')->cursor() as $campaign) {
                yield [$campaign->id, $campaign->name, $campaign->subject, $campaign->type, $campaign->status, $campaign->segment, $campaign->recipient_count, $campaign->sent_count, $campaign->failed_count, $campaign->skipped_count, $campaign->scheduled_at?->toDateTimeString(), $campaign->created_at?->toDateTimeString()];
            }
        });
    }

    private function emailDeliveries(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (EmailDelivery::query()->orderBy('id')->cursor() as $delivery) {
                yield [$delivery->id, $delivery->campaign_id, $delivery->email, $delivery->type, $delivery->status, $delivery->attempts, $delivery->last_error, $delivery->sent_at?->toDateTimeString(), $delivery->created_at?->toDateTimeString()];
            }
        });
    }

    private function automationRules(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (AutomationRule::query()->orderBy('id')->cursor() as $rule) {
                yield [$rule->id, $rule->key, $rule->name, $rule->event, $rule->enabled ? 'Igen' : 'Nem', $rule->delay_minutes, $rule->timezone, $rule->max_per_user, $rule->cooldown_minutes, $rule->next_run_at?->toDateTimeString()];
            }
        });
    }

    private function automationRuns(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (AutomationRun::query()->with(['rule', 'user'])->orderBy('id')->cursor() as $run) {
                yield [$run->id, $run->rule?->key, $run->event, $run->user?->email, $run->status, $run->attempts, $run->scheduled_for?->toDateTimeString(), $run->executed_at?->toDateTimeString(), $run->last_error, $run->created_at?->toDateTimeString()];
            }
        });
    }

    private function tickets(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (Ticket::query()->with(['user', 'order', 'assignee'])->orderBy('id')->cursor() as $ticket) {
                yield [$ticket->id, $ticket->ticket_number, $ticket->user?->name, $ticket->user?->email, $ticket->order?->displayNumber(), $ticket->type, $ticket->priority, $ticket->status, $ticket->assignee?->name, $ticket->response_due_at?->toDateTimeString(), $ticket->resolution_due_at?->toDateTimeString(), $ticket->last_reply_at?->toDateTimeString(), $ticket->created_at?->toDateTimeString()];
            }
        });
    }

    private function ticketMessages(): LazyCollection
    {
        return LazyCollection::make(function (): \Generator {
            foreach (TicketMessage::query()->with(['ticket', 'author'])->orderBy('id')->cursor() as $message) {
                yield [$message->id, $message->ticket?->ticket_number, $message->author?->name, $message->author?->email, $message->is_internal ? 'Igen' : 'Nem', mb_strlen($message->body), $message->attachments()->count(), $message->created_at?->toDateTimeString()];
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
