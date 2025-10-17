<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'color' => '#2563EB'],
            ['name' => 'Bonus', 'type' => 'income', 'color' => '#7C3AED'],
            ['name' => 'Refund', 'type' => 'income', 'color' => '#0EA5E9'],
            ['name' => 'Food', 'type' => 'expense', 'color' => '#F97316'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#F59E0B'],
            ['name' => 'Bills', 'type' => 'expense', 'color' => '#2563EB'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#EC4899'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#06B6D4'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
