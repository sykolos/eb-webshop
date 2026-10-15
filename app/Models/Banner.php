<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function scopeActive(Builder $query, string $slot = 'hero'): Builder
    {
        return $query->where('slot', $slot)
            ->where('is_published', true)
            ->where(fn (Builder $part) => $part->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $part) => $part->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')->orderByDesc('id');
    }

    public function imagePath(string $device): ?string
    {
        return match ($device) {
            'desktop' => $this->desktop_path,
            'tablet' => $this->tablet_path,
            'mobile' => $this->mobile_path,
            default => null,
        };
    }
}
