<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Models\User_shipping;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManualOrderService
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly OrderStatusService $statuses,
        private readonly AuditLogger $audit,
    ) {
    }

    public function create(User $admin, array $data): Order
    {
        try {
            return DB::transaction(function () use ($admin, $data): Order {
                $existing = Order::where('admin_idempotency_key', $data['idempotency_key'])->first();
                if ($existing) {
                    return $existing->load('items');
                }

                $partner = User::with('user_invoice')->whereKey($data['partner_id'])->first();
                if (!$partner || $partner->is_admin || ($partner->account_status ?? 'active') !== 'active') {
                    throw ValidationException::withMessages(['partner_id' => 'Csak aktív partner választható.']);
                }

                $invoice = $partner->user_invoice;
                foreach (['company_name', 'address', 'city', 'zipcode', 'country'] as $field) {
                    if (blank($invoice?->{$field})) {
                        throw ValidationException::withMessages(['partner_id' => 'A kiválasztott partner számlázási adatai hiányosak.']);
                    }
                }

                [$shipping, $shippingData] = $this->resolveShipping($partner, $data);
                [$lines, $subtotal, $vat, $gross] = $this->prepareLines($partner, $data['lines']);
                if ($lines === []) {
                    throw ValidationException::withMessages(['lines' => 'Legalább egy rendelési tétel szükséges.']);
                }

                $order = Order::create([
                    'user_id' => $partner->id,
                    'shipping_address_id' => $shipping?->id,
                    'admin_idempotency_key' => $data['idempotency_key'],
                    'created_by_admin_id' => $admin->id,
                    'status_responsible_id' => $admin->id,
                    'name' => $invoice->company_name,
                    'email' => $partner->email,
                    'phone' => $shippingData['phone'],
                    'shipping_receiver' => $shippingData['receiver'],
                    'address' => $shippingData['address'],
                    'city' => $shippingData['city'],
                    'state' => $invoice->state ?: '-',
                    'zipcode' => $shippingData['zipcode'],
                    'country' => $invoice->country ?: 'Magyarország',
                    'total' => $subtotal,
                    'subtotal_net' => $subtotal,
                    'vat_total' => $vat,
                    'gross_total' => $gross,
                    'status' => $data['initial_status'],
                    'status_note' => $data['status_note'] ?? null,
                    'note' => $data['note'] ?? null,
                    'payment_method' => (bool) $data['payment_method'],
                    'is_direct_shipping' => (bool) ($data['is_direct_shipping'] ?? false),
                ]);
                $order->forceFill(['order_number' => sprintf('EBR-%s-%06d', now()->format('Ymd'), $order->id)])->save();

                foreach ($lines as $line) {
                    $order->items()->create($line);
                }

                $this->statuses->recordInitial($order, $admin, $data['status_note'] ?? null);
                $this->audit->record('order.created_by_admin', $order, [
                    'partner_id' => $partner->id,
                    'item_count' => count($lines),
                    'idempotency_key' => $data['idempotency_key'],
                ]);

                return $order->load('items');
            });
        } catch (QueryException $exception) {
            $existing = Order::where('admin_idempotency_key', $data['idempotency_key'])->first();
            if ($existing) {
                return $existing->load('items');
            }

            throw $exception;
        }
    }

    private function resolveShipping(User $partner, array $data): array
    {
        if (!empty($data['shipping_address_id'])) {
            $shipping = User_shipping::whereKey($data['shipping_address_id'])->where('user_id', $partner->id)->first();
            if (!$shipping) {
                throw ValidationException::withMessages(['shipping_address_id' => 'A kiválasztott cím nem tartozik a partnerhez.']);
            }

            return [$shipping, [
                'receiver' => $shipping->receiver,
                'phone' => $shipping->phone,
                'address' => $shipping->address,
                'city' => $shipping->city,
                'zipcode' => $shipping->zipcode,
            ]];
        }

        return [null, [
            'receiver' => $data['shipping_receiver'],
            'phone' => $data['shipping_phone'],
            'address' => $data['shipping_address'],
            'city' => $data['shipping_city'],
            'zipcode' => $data['shipping_zipcode'],
        ]];
    }

    private function prepareLines(User $partner, array $rawLines): array
    {
        $grouped = collect($rawLines)->groupBy(fn (array $line) => (int) $line['product_id']);
        $products = Products::with('product_unit')->whereIn('id', $grouped->keys())->where('is_archived', false)->get()->keyBy('id');
        $context = $this->pricing->contextForUser($partner->id, $products);
        $lines = [];
        $subtotalMinor = 0;
        $vatMinor = 0;

        foreach ($grouped as $productId => $entries) {
            $product = $products->get($productId);
            if (!$product || $product->availability_status === 'out_of_stock') {
                throw ValidationException::withMessages(['lines' => 'A kiválasztott termék nem rendelhető.']);
            }

            $packCount = (int) $entries->sum(fn (array $line) => (int) $line['quantity']);
            if ($packCount < 1 || $packCount > 9999) {
                throw ValidationException::withMessages(['lines' => 'A csomagszám 1 és 9999 között lehet.']);
            }

            $quote = $this->pricing->quote($product, $packCount, $partner->id, $context);
            $subtotalMinor += $this->pricing->toMinorUnits($quote['line_net']);
            $vatMinor += $this->pricing->toMinorUnits($quote['vat']);
            $lines[] = [
                'product_id' => $product->id,
                'quantity' => $packCount * $quote['pack_quantity'],
                'unit_price' => $quote['unit_net'],
                'product_title' => $product->title,
                'serial_number' => $product->serial_number,
                'unit_measure' => $product->product_unit?->measure ?? 'db',
                'pack_quantity' => $quote['pack_quantity'],
                'line_net' => $quote['line_net'],
                'vat_amount' => $quote['vat'],
                'vat_rate' => $quote['vat_rate'],
            ];
        }

        return [
            $lines,
            $this->pricing->fromMinorUnits($subtotalMinor),
            $this->pricing->fromMinorUnits($vatMinor),
            $this->pricing->fromMinorUnits($subtotalMinor + $vatMinor),
        ];
    }
}
