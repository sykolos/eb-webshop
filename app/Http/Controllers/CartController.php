<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function addtocart(Request $request, int $id, int $q, string $m, CartService $carts)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $carts->add($request->user(), $id, (int) $validated['quantity']);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'A termék a kosárba került.',
                'count' => $carts->count($request->user()),
            ]);
        }

        return back()->with('addedtocart', 'A termék a kosárba került.');
    }

    public function removefromcart(Request $request, int $key, CartService $carts)
    {
        $carts->remove($request->user(), $key);

        return back()->with('success', 'Termék törölve.');
    }

    public function cartCount(Request $request, CartService $carts)
    {
        return response()->json(['count' => $carts->count($request->user())]);
    }

    public function previewcart(Request $request, CartService $carts)
    {
        $lines = $carts->lines($request->user());

        return view('pages.components.cart.cart-preview', [
            'lines' => $lines,
            'summary' => $carts->summary($request->user(), $lines),
        ]);
    }

    public function updateQuantity(Request $request, CartService $carts)
    {
        $validated = $request->validate([
            'key' => ['required', 'integer', 'min:1'],
            'change' => ['required', 'integer', 'in:-1,1'],
        ]);

        $carts->update($request->user(), (int) $validated['key'], (int) $validated['change']);
        $lines = $carts->lines($request->user());
        $line = collect($lines)->first(fn ($line) => (int) $line['item']->id === (int) $validated['key']);

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $carts->count($request->user()),
                'line' => $line ? [
                    'item_id' => $line['item']->id,
                    'quantity' => $line['quantity'],
                    'line_net' => $line['quote']['line_net'] ?? null,
                ] : null,
                'summary' => $carts->summary($request->user(), $lines),
            ]);
        }

        return response()->noContent();
    }

    public function removeItem(Request $request, CartService $carts)
    {
        $validated = $request->validate([
            'key' => ['required', 'integer', 'min:1'],
        ]);

        $carts->remove($request->user(), (int) $validated['key']);

        if ($request->expectsJson()) {
            return response()->json([
                'count' => $carts->count($request->user()),
            ]);
        }

        return response()->noContent();
    }
}
