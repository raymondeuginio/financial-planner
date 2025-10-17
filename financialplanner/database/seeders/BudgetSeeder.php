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

        $defaultBudgets = [
            'Makanan & Minuman' => 2500000,
            'Transportasi' => 750000,
            'Tagihan Rumah' => 1800000,
            'Belanja Rumah Tangga' => 1200000,
            'Hiburan' => 900000,
        ];

        foreach ($categories as $category) {
            $amount = $defaultBudgets[$category->name] ?? 1000000;

            Budget::updateOrCreate(
                ['category_id' => $category->id, 'month' => $month],
                ['amount' => $amount]
            );
        }
    }
}
