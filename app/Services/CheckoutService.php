<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Products;
use App\Models\User_invoce;
use App\Models\User_shipping;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function createOrder(User $user, array $data, array $cart): Order
    {
        $existing = $this->findByToken($user, $data['checkout_token']);

        if ($existing) {
            return $existing;
        }

        if (count($cart) === 0) {
            throw ValidationException::withMessages([
                'cart' => 'Üres a kosarad.',
            ]);
        }

        $shipping = User_shipping::where('id', $data['shipping_address_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$shipping) {
            throw ValidationException::withMessages([
                'shipping_address_id' => 'A kiválasztott szállítási cím nem található.',
            ]);
        }

        $invoice = User_invoce::where('user_id', $user->id)->first();

        $missingInvoiceData = !$invoice;
        foreach (['company_name', 'address', 'city', 'state', 'zipcode', 'country', 'vatnumber'] as $field) {
            $missingInvoiceData = $missingInvoiceData || blank($invoice?->{$field});
        }

        if ($missingInvoiceData) {
            throw ValidationException::withMessages([
                'billing' => 'A rendeléshez hiányoznak a számlázási adatok.',
            ]);
        }

        [$lines, $total] = $this->prepareLines($user, $cart);

        try {
            return DB::transaction(function () use ($user, $data, $shipping, $invoice, $lines, $total) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'shipping_address_id' => $shipping->id,
                    'checkout_token' => $data['checkout_token'],
                    'name' => $invoice->company_name,
                    'email' => $user->email,
                    'phone' => $shipping->phone,
                    'address' => $shipping->address,
                    'city' => $shipping->city,
                    'state' => '-',
                    'zipcode' => $shipping->zipcode,
                    'country' => 'Magyarország',
                    'total' => $total,
                    'status' => 'függőben',
                    'note' => $data['note'] ?? null,
                    'payment_method' => (bool) $data['payment_method'],
                    'is_direct_shipping' => (bool) ($data['is_direct_shipping'] ?? false),
                ]);

                foreach ($lines as $line) {
                    $order->items()->create($line);
                }

                return $order;
            });
        } catch (QueryException $exception) {
            // A unique token megvédi a párhuzamos dupla beküldést is.
            $existing = $this->findByToken($user, $data['checkout_token']);

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function prepareLines(User $user, array $cart): array
    {
        $lines = [];
        $total = 0;

        foreach ($cart as $item) {
            $productId = $item['product']['id'] ?? null;
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if (!$productId || !$quantity) {
                throw ValidationException::withMessages([
                    'cart' => 'A kosár egyik tétele már nem érvényes.',
                ]);
            }

            $product = Products::with(['special_prices', 'product_unit'])->find($productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    'cart' => 'A kosár egyik terméke már nem elérhető.',
                ]);
            }

            $packagingQuantity = (int) ($product->product_unit?->quantity ?: 1);
            $unitPrice = $product->getPriceForUser($user->id);
            $lineQuantity = $quantity * $packagingQuantity;

            $lines[] = [
                'product_id' => $product->id,
                'quantity' => $lineQuantity,
                'unit_price' => $unitPrice,
            ];
            $total += $unitPrice * $lineQuantity;
        }

        return [$lines, $total];
    }

    private function findByToken(User $user, string $token): ?Order
    {
        return Order::where('user_id', $user->id)
            ->where('checkout_token', $token)
            ->first();
    }
}
