<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 50; $i++) {
            Product::create([
                'name' => $faker->words(3, true),
                'sku' => strtoupper(Str::random(10)),
                'description' => $faker->sentence(),
                'price' => $faker->randomFloat(2, 10, 1000),
                'initial_quantity' => $faker->numberBetween(1, 500),
            ]);
        }
    }
}
