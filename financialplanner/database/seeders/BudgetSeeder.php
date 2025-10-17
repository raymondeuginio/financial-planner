<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $month = Carbon::now()->startOfMonth()->toDateString();

        $amounts = [
            'Food' => 2000000,
            'Transport' => 800000,
            'Bills' => 1500000,
            'Shopping' => 1000000,
            'Entertainment' => 700000,
        ];

        foreach ($amounts as $name => $amount) {
            $category = Category::where('name', $name)->first();
            if ($category) {
                Budget::updateOrCreate(
                    ['category_id' => $category->id, 'month' => $month],
                    ['amount' => $amount]
                );
            }
        }
    }
}
