<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Products;
use App\Services\CategoryTreeService;
use App\Services\OfferService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfferAdminController extends Controller
{
    public function index(CategoryTreeService $tree)
    {
        return view('admin.m20.offers.index', [
            'offers' => Offer::query()->withCount(['products', 'categories'])->orderBy('sort_order')->orderByDesc('id')->paginate(15),
            'categories' => $tree->flatten($tree->roots()),
            'products' => Products::query()->published()->orderBy('title')->get(['id', 'title', 'serial_number']),
        ]);
    }

    public function store(Request $request, OfferService $offers)
    {
        $offers->save($this->validated($request), $request->user());
        return redirect()->route('adminpanel.offers')->with('success', 'Ajánlat mentve.');
    }

    public function edit(Offer $offer, CategoryTreeService $tree)
    {
        return view('admin.m20.offers.form', [
            'offer' => $offer->load(['products', 'categories']),
            'categories' => $tree->flatten($tree->roots()),
            'products' => Products::query()->published()->orderBy('title')->get(['id', 'title', 'serial_number']),
        ]);
    }

    public function update(Request $request, Offer $offer, OfferService $offers)
    {
        $offers->save($this->validated($request, $offer), $request->user(), $offer);
        return redirect()->route('adminpanel.offers')->with('success', 'Ajánlat frissítve.');
    }

    public function publish(Offer $offer, OfferService $offers, Request $request)
    {
        $offers->setPublished($offer, true, $request->user());
        return back()->with('success', 'Ajánlat publikálva.');
    }

    public function archive(Offer $offer, OfferService $offers, Request $request)
    {
        $offers->setPublished($offer, false, $request->user());
        return back()->with('success', 'Ajánlat visszavonva.');
    }

    private function validated(Request $request, ?Offer $offer = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191', Rule::unique('offers', 'slug')->ignore($offer?->id)],
            'summary' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_published' => ['nullable', 'boolean'],
            'product_ids' => ['nullable', 'array', 'max:500'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'category_ids' => ['nullable', 'array', 'max:100'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
        ]);
    }
}
