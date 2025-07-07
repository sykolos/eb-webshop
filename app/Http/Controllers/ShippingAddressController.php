<?php
namespace App\Http\Controllers;

use App\Models\User_shipping;
use Illuminate\Http\Request;

class ShippingAddressController extends Controller
{
    public function create()
    {
        return view('pages.components.account.shipping.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'zipcode' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);

        $request->user()->user_shipping()->create($validated);
        return redirect()->route('account.modify')->with('success', 'Új szállítási cím hozzáadva.');
    }

    public function edit(User_shipping $address)
    {
        $this->authorizeAccess($address);
        return view('pages.components.account.shipping.edit', compact('address'));
    }

    public function update(Request $request, User_shipping $address)
    {
        $this->authorizeAccess($address);

        $validated = $request->validate([
            'receiver' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'zipcode' => 'required|string|max:10',
            'city' => 'required|string|max:100',
            'address' => 'required|string|max:255',
            'comment' => 'nullable|string|max:255',
        ]);

        $address->update($validated);

        return redirect()->route('account.modify')->with('success', 'Szállítási cím frissítve.');
    }

    public function destroy(User_shipping $address)
    {
        $this->authorizeAccess($address);
        $address->delete();

        return redirect()->route('account.modify')->with('success', 'Szállítási cím törölve.');
    }

    protected function authorizeAccess(User_shipping $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403, 'Nincs jogosultságod ehhez a címhez.');
        }
    }
}
