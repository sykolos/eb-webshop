<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Products;
use App\Models\User;
use App\Models\User_invoce;
use App\Models\User_shipping;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(private readonly PricingService $pricing)
    {
    }

    public function createOrder(User $user, array $data, Cart|array $cart): Order
    {
        try {
            return DB::transaction(function () use ($user, $data, $cart) {
                $existing = $this->findByToken($user, $data['checkout_token']);
                if ($existing) {
                    return $existing;
                }

                $shipping = User_shipping::whereKey($data['shipping_address_id'])
                    ->where('user_id', $user->id)
                    ->first();
                if (!$shipping) {
                    throw ValidationException::withMessages([
                        'shipping_address_id' => 'A kiválasztott szállítási cím nem található.',
                    ]);
                }

                $invoice = User_invoce::where('user_id', $user->id)->first();
                foreach (['company_name', 'address', 'city', 'state', 'zipcode', 'country', 'vatnumber'] as $field) {
                    if (blank($invoice?->{$field})) {
                        throw ValidationException::withMessages([
                            'billing' => 'A rendeléshez hiányoznak a számlázási adatok.',
                        ]);
                    }
                }

                [$lines, $subtotal, $vat, $gross] = $cart instanceof Cart
                    ? $this->prepareDatabaseLines($user, $cart)
                    : $this->prepareLegacyLines($user, $cart);

                if ($lines === []) {
                    throw ValidationException::withMessages(['cart' => 'Üres a kosarad.']);
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'shipping_address_id' => $shipping->id,
                    'checkout_token' => $data['checkout_token'],
                    'name' => $invoice->company_name,
                    'email' => $user->email,
                    'phone' => $shipping->phone,
                    'address' => $shipping->address,
                    'city' => $shipping->city,
                    'state' => $invoice->state ?: '-',
                    'zipcode' => $shipping->zipcode,
                    'country' => $invoice->country ?: 'Magyarország',
                    'total' => $subtotal,
                    'subtotal_net' => $subtotal,
                    'vat_total' => $vat,
                    'gross_total' => $gross,
                    'status' => 'függőben',
                    'note' => $data['note'] ?? null,
                    'payment_method' => (bool) $data['payment_method'],
                    'is_direct_shipping' => (bool) ($data['is_direct_shipping'] ?? false),
                ]);

                $order->forceFill([
                    'order_number' => sprintf('EBR-%s-%06d', now()->format('Ymd'), $order->id),
                ])->save();

                foreach ($lines as $line) {
                    $order->items()->create($line);
                }

                // Preserve wasRecentlyCreated so the controller can clear the
                // cart and dispatch notifications only for a new order.
                return $order->load('items');
            });
        } catch (QueryException $exception) {
            // The unique checkout token makes parallel submissions idempotent.
            $existing = $this->findByToken($user, $data['checkout_token']);
            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function prepareDatabaseLines(User $user, Cart $cart): array
    {
        $items = $cart->items()
            ->with('product.product_unit')
            ->lockForUpdate()
            ->get();

        $rawCart = $items->map(fn ($item) => [
            'product' => ['id' => $item->product_id],
            'quantity' => $item->quantity,
        ])->all();

        return $this->prepareLines($user, $rawCart);
    }

    private function prepareLegacyLines(User $user, array $cart): array
    {
        return $this->prepareLines($user, $cart);
    }

    private function prepareLines(User $user, array $cart): array
    {
        $lines = [];
        $subtotalMinor = 0;
        $vatMinor = 0;

        foreach ($cart as $item) {
            $productId = (int) data_get($item, 'product.id');
            $packCount = filter_var(data_get($item, 'quantity'), FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if (!$productId || !$packCount) {
                throw ValidationException::withMessages(['cart' => 'A kosár egyik tétele már nem érvényes.']);
            }

            $product = Products::published()->with('product_unit')->find($productId);
            if (!$product || $product->availability_status === 'out_of_stock') {
                throw ValidationException::withMessages([
                    'cart' => 'A kosár egyik terméke már nem elérhető, frissítsd a kosarat.',
                ]);
            }

            $quote = $this->pricing->quote($product, (int) $packCount, $user->id);
            $lineNetMinor = $this->pricing->toMinorUnits($quote['line_net']);
            $lineVatMinor = $this->pricing->toMinorUnits($quote['vat']);
            $subtotalMinor += $lineNetMinor;
            $vatMinor += $lineVatMinor;

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

    private function findByToken(User $user, string $token): ?Order
    {
        return Order::where('user_id', $user->id)
            ->where('checkout_token', $token)
            ->first();
    }
}
