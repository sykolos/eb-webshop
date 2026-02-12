<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. KATEGÓRIÁK feltöltése
        $categories = [
            ['id' => 1, 'name' => 'Wagok'],
            ['id' => 2, 'name' => 'Kiegészítők'],
            ['id' => 3, 'name' => 'Elektronika'],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->updateOrInsert(['id' => $cat['id']], [
                'name' => $cat['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. FELHASZNÁLÓK (Admin jelszóval: 123)
        // Admin - Az email az azonosító az updateOrInsert-nél
        DB::table('users')->updateOrInsert(['email' => 'admin@ebusiness.hu'], [
            'name' => 'Adminisztrátor',
            'password' => Hash::make('123'),
            'is_admin' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Teszt Ügyfél az árazási mátrixhoz
        DB::table('users')->updateOrInsert(['email' => 'teszt@ugyfel.hu'], [
            'name' => 'Teszt Ügyfél Kft.',
            'password' => Hash::make('123'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. TERMÉKEK (A te migrationöd alapján, units tábla nélkül)
        for ($i = 1; $i <= 15; $i++) {
            DB::table('products')->updateOrInsert(['serial_number' => 'SN-00' . $i], [
                'title' => 'Teszt Termék ' . $i,
                'description' => 'Ez egy minta leírás a termékhez.',
                'price' => rand(1000, 25000),
                'category_id' => rand(1, 3),
                'unit_id' => 1, // Fix érték, mivel a mező kötelező, de tábla nem kell hozzá
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}