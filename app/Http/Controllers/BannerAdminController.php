<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Services\AuditLogger;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BannerAdminController extends Controller
{
    public function index(BannerService $banners)
    {
        return view('admin.m14.banners.index', [
            'banners' => Banner::query()->orderBy('slot')->orderBy('sort_order')->orderByDesc('id')->paginate(15),
            'sizes' => $banners->sizes(),
        ]);
    }

    public function store(Request $request, BannerService $banners, AuditLogger $audit)
    {
        $data = $this->validated($request, true);
        try {
            $banner = $banners->save($data, $this->files($request), null, $request->boolean('publish'));
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['publish' => $exception->getMessage()]);
        }
        $audit->record('banner.created', $banner, ['published' => $banner->is_published]);
        return back()->with('success', $banner->is_published ? 'Banner mentve és publikálva.' : 'Banner piszkozatként mentve.');
    }

    public function publish(Banner $banner, BannerService $banners, AuditLogger $audit)
    {
        try {
            $banner = $banners->publish($banner);
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['publish' => $exception->getMessage()]);
        }
        $audit->record('banner.published', $banner);
        return back()->with('success', 'Banner publikálva.');
    }

    public function archive(Banner $banner, BannerService $banners, AuditLogger $audit)
    {
        $banners->archive($banner);
        $audit->record('banner.archived', $banner);
        return back()->with('success', 'Banner levéve a publikus felületről.');
    }

    public function destroy(Banner $banner, BannerService $banners, AuditLogger $audit)
    {
        $id = $banner->id;
        $banners->delete($banner);
        $audit->record('banner.deleted', null, ['banner_id' => $id]);
        return back()->with('success', 'Banner törölve.');
    }

    private function validated(Request $request, bool $creating): array
    {
        $rules = [
            'slot' => ['required', 'in:hero,floating'],
            'title' => ['nullable', 'string', 'max:191'],
            'alt_text' => ['required', 'string', 'max:191'],
            'link_url' => ['nullable', 'string', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'desktop' => [$creating ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'tablet' => [$creating ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'mobile' => [$creating ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
        return $request->validate($rules);
    }

    private function files(Request $request): array
    {
        return collect(['desktop', 'tablet', 'mobile'])->mapWithKeys(fn (string $device) => [$device => $request->file($device)])->all();
    }
}
