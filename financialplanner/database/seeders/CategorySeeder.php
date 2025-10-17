<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => 'income', 'color' => '#0ea5e9'],
            ['name' => 'Bonus', 'type' => 'income', 'color' => '#6366f1'],
            ['name' => 'Refund', 'type' => 'income', 'color' => '#22d3ee'],
            ['name' => 'Food', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Transport', 'type' => 'expense', 'color' => '#14b8a6'],
            ['name' => 'Bills', 'type' => 'expense', 'color' => '#f43f5e'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#f59e0b'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                ['color' => $category['color']]
            );
        }
    }
}
