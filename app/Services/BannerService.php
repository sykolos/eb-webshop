<?php

namespace App\Services;

use App\Models\Banner;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

final class BannerService
{
    public const SLOTS = ['hero', 'floating'];

    private const SIZES = [
        'desktop' => [1600, 500],
        'tablet' => [1200, 675],
        'mobile' => [900, 1125],
    ];

    public function active(string $slot = 'hero'): ?Banner
    {
        return Banner::query()->with('offer')->active($slot)->first();
    }

    public function save(array $attributes, array $files, ?Banner $banner = null, bool $publish = false): Banner
    {
        return DB::transaction(function () use ($attributes, $files, $banner, $publish): Banner {
            $record = $banner ?: new Banner();
            $record->fill([
                'slot' => $attributes['slot'],
                'title' => filled($attributes['title'] ?? null) ? trim($attributes['title']) : null,
                'alt_text' => trim($attributes['alt_text']),
                'link_url' => filled($attributes['link_url'] ?? null) ? trim($attributes['link_url']) : null,
                'link_label' => filled($attributes['link_label'] ?? null) ? trim($attributes['link_label']) : null,
                'offer_id' => $attributes['offer_id'] ?? null,
                'sort_order' => (int) ($attributes['sort_order'] ?? 0),
                'starts_at' => $attributes['starts_at'] ?? null,
                'ends_at' => $attributes['ends_at'] ?? null,
                'created_by' => $record->created_by ?: auth()->id(),
                'is_published' => $publish,
            ]);

            foreach (array_keys(self::SIZES) as $device) {
                if (isset($files[$device]) && $files[$device] instanceof UploadedFile) {
                    $old = $record->imagePath($device);
                    $path = $this->storeVariant($files[$device], $device);
                    $record->{$device.'_path'} = $path;
                    if ($old) {
                        Storage::disk('public')->delete($old);
                    }
                }
            }

            if ($publish) {
                foreach (array_keys(self::SIZES) as $device) {
                    if (! filled($record->{$device.'_path'})) {
                        throw new \InvalidArgumentException('Publikáláshoz mindhárom képeszköz-változat szükséges.');
                    }
                }
            }

            $record->save();
            return $record->refresh();
        });
    }

    public function publish(Banner $banner): Banner
    {
        foreach (array_keys(self::SIZES) as $device) {
            if (! filled($banner->{$device.'_path'})) {
                throw new \InvalidArgumentException('Publikáláshoz mindhárom képeszköz-változat szükséges.');
            }
        }

        $banner->update(['is_published' => true]);
        return $banner->refresh();
    }

    public function archive(Banner $banner): void
    {
        $banner->update(['is_published' => false]);
    }

    public function delete(Banner $banner): void
    {
        foreach (array_keys(self::SIZES) as $device) {
            $path = $banner->imagePath($device);
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
        $banner->delete();
    }

    public function sizes(): array
    {
        return self::SIZES;
    }

    private function storeVariant(UploadedFile $file, string $device): string
    {
        [$width, $height] = self::SIZES[$device];
        $path = 'banners/'.Str::uuid().'/'.$device.'.webp';
        Storage::disk('public')->put($path, Image::read($file)->cover($width, $height)->toWebp(86));
        return $path;
    }
}
