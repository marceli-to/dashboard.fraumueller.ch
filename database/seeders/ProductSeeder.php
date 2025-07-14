<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            'Erste Ausgabe',
            'Jahresabo 2026', 
            'Jahresabo Erstausgabe',
            'Jahresabo',
            'Geschenk-Abo',
        ];

        foreach ($products as $productName) {
            Product::firstOrCreate(['name' => $productName]);
        }
    }
}
