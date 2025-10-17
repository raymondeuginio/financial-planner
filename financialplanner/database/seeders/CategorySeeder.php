<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Gaji Pokok', 'type' => 'income', 'color' => '#0ea5e9'],
            ['name' => 'Bonus Kinerja', 'type' => 'income', 'color' => '#6366f1'],
            ['name' => 'Pengembalian Dana', 'type' => 'income', 'color' => '#22d3ee'],
            ['name' => 'Makanan & Minuman', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Transportasi', 'type' => 'expense', 'color' => '#14b8a6'],
            ['name' => 'Tagihan Rumah', 'type' => 'expense', 'color' => '#f43f5e'],
            ['name' => 'Belanja Rumah Tangga', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Hiburan', 'type' => 'expense', 'color' => '#f59e0b'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                ['color' => $category['color']]
            );
        }
    }
}
