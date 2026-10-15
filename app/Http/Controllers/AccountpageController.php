<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Products;
use App\Models\User_invoce;
use App\Models\User_shipping;
use App\Services\AccountService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountpageController extends Controller
{
    public function dashboard(AccountService $accounts)
    {
        return view('pages.components.account.userinfo', $accounts->dashboard(auth()->user()));
    }

    public function orders(Request $request)
    {
        $orders = auth()->user()->orders()->latest();
        if ($request->filled('status')) {
            $orders->where('status', $request->string('status'));
        }
        if ($request->filled('from')) {
            $orders->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $orders->whereDate('created_at', '<=', $request->date('to'));
        }

        return view('pages.components.account.orders', [
            'orders' => $orders->paginate(10)->withQueryString(),
            'statuses' => Order::allowedStatuses(),
        ]);
    }

    public function userinfo()
    {
        return redirect()->route('account');
    }

    public function details($id)
    {
        $status = ['függőben', 'feldolgozás', 'kiszállítva', 'törölve'];
        $order = Order::with('user', 'items', 'items.product', 'user_shipping')->findOrFail($id);
        $this->authorize('view', $order);
        return view('pages.components.account.details', ['order' => $order, 'states' => $status, 'id' => $id]);
    }

    public function modify()
    {
        app(AccountService::class)->ensureDefaultAddress(auth()->user());
        $user = auth()->user()->load(['user_invoice', 'user_shipping', 'preference']);
        $preferences = app(AccountService::class)->preferences($user);
        return view('pages.components.account.modify', compact('user', 'preferences'));
    }

    public function edit(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'company_name' => ['required', 'max:191'],
            'vatnumber' => ['required', 'max:191'],
            'country' => ['required', 'max:191'],
            'state' => ['required'],
            'zipcode' => ['required'],
            'city' => ['required', 'max:191'],
            'address' => ['required', 'max:191'],
            'order_updates' => ['nullable', 'boolean'],
            'newsletter_subscribed' => ['nullable', 'boolean'],
            'marketing_notifications' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        $emailChanged = $user->email !== $data['email'];
        $user->forceFill([
            'name' => $data['username'],
            'email' => $data['email'],
            'email_verified_at' => $emailChanged ? null : $user->email_verified_at,
        ])->save();

        $invoice = User_invoce::firstOrCreate(['user_id' => $user->id], [
            'company_name' => '', 'address' => '', 'vatnumber' => '', 'country' => '',
            'state' => '', 'zipcode' => '', 'city' => '',
        ]);
        $invoice->update([
            'company_name' => $data['company_name'], 'vatnumber' => $data['vatnumber'],
            'country' => $data['country'], 'state' => $data['state'],
            'zipcode' => $data['zipcode'], 'city' => $data['city'], 'address' => $data['address'],
        ]);

        app(AccountService::class)->updatePreferences($user, [
            'order_updates' => $request->boolean('order_updates'),
            'newsletter_subscribed' => $request->boolean('newsletter_subscribed'),
            'marketing_notifications' => $request->boolean('marketing_notifications'),
        ]);

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('account.modify')->with('success', $emailChanged
            ? 'Adatok frissítve. Az új email címre ellenőrző linket küldtünk.'
            : 'Adatok frissítve');
    }

    public function getpdf($id)
    {
        $order = Order::with('user', 'items', 'items.product')->findOrFail($id);
        $this->authorize('view', $order);
        $pdf = Pdf::loadView('myPDF', ['order' => $order, 'states' => Order::statuses(), 'id' => $id]);
        return $pdf->download('document.pdf');
    }

    public function reorder(Order $order, AccountService $accounts)
    {
        $result = $accounts->reorder(auth()->user(), $order);
        $message = count($result['added']) . ' tétel bekerült a kosárba.';
        if ($result['skipped']) {
            $message .= ' Nem rendelhető: ' . implode(', ', $result['skipped']);
        }
        return redirect()->route('cart')->with('success', $message);
    }

    public function favorites()
    {
        return view('pages.components.account.favorites', [
            'products' => auth()->user()->favoriteProducts()->with('product_unit')->latest('user_favorites.created_at')->get(),
        ]);
    }

    public function addFavorite(int $productId, AccountService $accounts)
    {
        $product = Products::findOrFail($productId);
        $added = $accounts->toggleFavorite(auth()->user(), $product);
        return back()->with('success', $added ? 'Termék elmentve a kedvencek közé.' : 'Termék eltávolítva a kedvencek közül.');
    }

    public function setDefaultShipping(User_shipping $address, AccountService $accounts)
    {
        $accounts->setDefaultAddress(auth()->user(), $address);
        return back()->with('success', 'Alapértelmezett szállítási cím frissítve.');
    }
}
