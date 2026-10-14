<?php

namespace App\Http\Controllers;

use App\Models\ProductMedia;
use App\Models\Products;
use App\Services\AuditLogger;
use App\Services\ProductCatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductMediaController extends Controller
{
    public function store(Request $request, int $productId, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $product = Products::findOrFail($productId);
        $data = $request->validate([
            'media' => ['required', 'file', 'mimes:jpeg,jpg,png,webp,gif,pdf', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);
        $media = $catalog->storeMedia($product, $data['media'], isset($data['sort_order']) ? (int) $data['sort_order'] : null);
        $audit->record('product.media.created', $media, [
            'product_id' => $product->id,
            'kind' => $media->kind,
        ]);

        return back()->with('success', 'Médiafájl feltöltve.');
    }

    public function update(Request $request, int $productId, int $mediaId, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $media = ProductMedia::where('product_id', $productId)->findOrFail($mediaId);
        $data = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $media = $catalog->updateMedia($media, (int) $data['sort_order'], (bool) ($data['is_public'] ?? false));
        $audit->record('product.media.updated', $media, [
            'product_id' => $productId,
            'sort_order' => $media->sort_order,
            'is_public' => $media->is_public,
        ]);

        return back()->with('success', 'Médiafájl beállításai frissítve.');
    }

    public function destroy(int $productId, int $mediaId, ProductCatalogService $catalog, AuditLogger $audit)
    {
        $media = ProductMedia::where('product_id', $productId)->findOrFail($mediaId);
        $catalog->deleteMedia($media);
        $audit->record('product.media.deleted', null, [
            'product_id' => $productId,
            'media_id' => $mediaId,
        ]);

        return back()->with('success', 'Médiafájl törölve.');
    }

    public function download(int $productId, int $mediaId)
    {
        $media = ProductMedia::where('product_id', $productId)->findOrFail($mediaId);
        abort_unless($media->disk === 'private', 404);
        abort_unless(Storage::disk('private')->exists($media->path), 404);

        return Storage::disk('private')->download($media->path, $media->original_name ?: basename($media->path));
    }
}
