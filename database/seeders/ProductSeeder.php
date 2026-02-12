<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Products::create([
            'serial_number' => 'PROD-100',
            'title' => 'Minta Termék',
            'description' => 'Teszt leírás',
            'price' => 5000,
            'category_id' => 1, // Fontos: Győződj meg róla, hogy létezik ilyen ID a categories táblában!
            'unit_id' => 1      // Fontos: Győződj meg róla, hogy létezik ilyen ID a units táblában!
    ]);
}
}
