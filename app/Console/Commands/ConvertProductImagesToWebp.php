<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use App\Models\Products;

class ConvertProductImagesToWebp extends Command
{
    protected $signature = 'products:convert-images-to-webp';
    protected $description = 'Átkonvertálja a products tábla képeit WebP formátumba és frissíti az elérési utat';

    public function handle()
    {
        $products = Products::all();
        $converted = 0;

        foreach ($products as $product) {
            $oldPath = $product->image;

            // csak akkor próbáljuk meg, ha a fájl létezik
            if (!$oldPath || !Storage::disk('public')->exists($oldPath)) {
                $this->warn("Kép nem található: {$oldPath}");
                continue;
            }

            // csak ha nem webp
            if (Str::endsWith($oldPath, '.webp')) {
                $this->info("Már WebP: {$oldPath}");
                continue;
            }

            // fájl betöltése
            $imageData = Storage::disk('public')->get($oldPath);
            $image = Image::make($imageData)->encode('webp', 90);

            // új fájlnév
            $newPath = 'products/' . Str::uuid() . '.webp';
            Storage::disk('public')->put($newPath, $image);

            // adatbázis frissítése
            $product->image = $newPath;
            $product->save();

            // régi fájl törlése (opcionális)
            Storage::disk('public')->delete($oldPath);

            $this->info("Átkonvertálva: {$oldPath} -> {$newPath}");
            $converted++;
        }

        $this->info("✅ Kész: {$converted} kép konvertálva WebP-re.");
        return Command::SUCCESS;
    }
}
