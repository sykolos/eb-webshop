<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\FilterDefinition;
use App\Models\Products;
use App\Models\product_unit;
use App\Services\AuditLogger;
use App\Services\CategoryTreeService;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ProductCatalogService $catalog, CategoryTreeService $tree)
    {
        $products = $catalog->query($request->only([
            'search', 'category_id', 'availability_status', 'published', 'include_archived',
        ]))->latest()->paginate(20)->onEachSide(0)->appends($request->query());
        $units = product_unit::where('is_active', true)->orderBy('unit')->get();
        $categories = $tree->flatten($tree->roots());

        if ($request->ajax()) {
            return view('admin.m06.products.partials.list', compact('products', 'units'))->render();
        }

        return view('admin.m06.products.index', compact('products', 'units', 'categories'));
    }

    public function create(CategoryTreeService $tree)
    {
        return view('admin.m06.products.form', [
            'product' => null,
            'product_units' => product_unit::where('is_active', true)->orderBy('unit')->get(),
            'categories' => $tree->flatten($tree->roots()),
            'filter_definitions' => FilterDefinition::with(['values' => fn ($query) => $query->where('is_active', true)])->where('is_active', true)->orderBy('sort_order')->get(),
            'mode' => 'create',
        ]);
    }

    public function store(ProductStoreRequest $request, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $product = $catalog->create($request->validated(), $request->file('image'));
        $audit->record('product.created', $product, [
            'title' => $product->title,
            'serial_number' => $product->serial_number,
            'category_id' => $product->category_id,
            'availability_status' => $product->availability_status,
        ]);

        return redirect()->route('adminpanel.products.edit', $product->id)->with('success', 'Termék létrehozva.');
    }

    public function edit(int $id, CategoryTreeService $tree)
    {
        $product = Products::with(['media', 'filterValues', 'availabilityHistories.changedBy'])->findOrFail($id);

        return view('admin.m06.products.form', [
            'product_units' => product_unit::where('is_active', true)->orderBy('unit')->get(),
            'categories' => $tree->flatten($tree->roots()),
            'product' => $product,
            'filter_definitions' => FilterDefinition::with(['values' => fn ($query) => $query->where('is_active', true)])->where('is_active', true)->orderBy('sort_order')->get(),
            'mode' => 'edit',
        ]);
    }

    public function update(ProductUpdateRequest $request, int $id, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $product = Products::findOrFail($id);
        $product = $catalog->update($product, $request->validated(), $request->file('image'));
        $audit->record('product.updated', $product, [
            'title' => $product->title,
            'serial_number' => $product->serial_number,
            'category_id' => $product->category_id,
            'availability_status' => $product->availability_status,
        ]);

        return back()->with('success', 'Termék frissítve.');
    }

    public function destroy(int $id, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $product = Products::findOrFail($id);
        $catalog->archive($product);
        $audit->record('product.archived', $product, ['title' => $product->title]);

        return back()->with('success', 'Termék archiválva. A rendelési előzmények megmaradtak.');
    }

    public function restore(int $id, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $product = Products::findOrFail($id);
        $catalog->restore($product);
        $audit->record('product.restored', $product, ['title' => $product->title]);

        return back()->with('success', 'Termék visszaállítva.');
    }

    public function dataQuality(ProductCatalogService $catalog)
    {
        return view('admin.m06.products.data-quality', ['report' => $catalog->dataQualityReport()]);
    }
}
