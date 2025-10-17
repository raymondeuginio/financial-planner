<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::where('type', 'expense')->get();
        $startOfYear = Carbon::now()->startOfYear();
        $months = collect(range(0, 11))->map(fn ($i) => first_day_of_month($startOfYear->copy()->addMonths($i)));

        $baseBudgets = [
            'Makan & Minum' => 2500000,
            'Transportasi' => 850000,
            'Tagihan Rumah Tangga' => 1800000,
            'Belanja Pribadi' => 1200000,
            'Hiburan' => 900000,
        ];

        foreach ($months as $index => $month) {
            foreach ($categories as $category) {
                $base = $baseBudgets[$category->name] ?? 1000000;
                $adjustment = ($index % 3) * 50000;
                $amount = $base + $adjustment;

                Budget::updateOrCreate(
                    ['category_id' => $category->id, 'month' => $month],
                    ['amount' => $amount]
                );
            }
        }
    }
}
