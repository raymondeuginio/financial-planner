<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $month = first_day_of_month(now());
        $categories = Category::where('type', 'expense')->get();

        foreach ($categories as $category) {
            Budget::updateOrCreate(
                ['category_id' => $category->id, 'month' => $month],
                ['amount' => random_int(800000, 2500000)]
            );
        }
    }
}
