<?php

namespace App\Services;

use App\Models\product_unit;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UnitCatalogService
{
    public function create(array $attributes): product_unit
    {
        return product_unit::create([
            'unit' => $attributes['unit'],
            'quantity' => (int) $attributes['quantity'],
            'measure' => $attributes['measure'],
            'slug' => $this->uniqueSlug($attributes['unit']),
            'is_active' => (bool) ($attributes['is_active'] ?? true),
        ]);
    }

    public function update(product_unit $unit, array $attributes): product_unit
    {
        $unit->update([
            'unit' => $attributes['unit'],
            'quantity' => (int) $attributes['quantity'],
            'measure' => $attributes['measure'],
            'slug' => $this->uniqueSlug($attributes['unit'], $unit),
            'is_active' => (bool) ($attributes['is_active'] ?? false),
        ]);

        return $unit->refresh();
    }

    public function delete(product_unit $unit): void
    {
        if ($unit->products()->exists()) {
            throw ValidationException::withMessages(['unit' => 'Használt mennyiségi egység nem törölhető. Előbb rendelj másik egységet a termékekhez.']);
        }

        $unit->delete();
    }

    private function uniqueSlug(string $value, ?product_unit $ignore = null): string
    {
        $base = Str::slug($value) ?: 'egyseg';
        $slug = $base;
        $counter = 2;

        while (product_unit::where('slug', $slug)
            ->when($ignore, fn ($query) => $query->where('id', '!=', $ignore->id))
            ->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
