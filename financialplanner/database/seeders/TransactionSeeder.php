<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $incomeCategories = Category::where('type', 'income')->pluck('id');
        $expenseCategories = Category::where('type', 'expense')->pluck('id');
        $wallets = Wallet::all();

        if ($wallets->isEmpty() || $incomeCategories->isEmpty() || $expenseCategories->isEmpty()) {
            return;
        }

        $startDate = Carbon::now()->subMonths(3)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $dates = [];
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        $transactions = [];

        foreach ($dates as $date) {
            // Income events few times a month
            if ($date->isStartOfMonth()) {
                $transactions[] = [
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $incomeCategories->random(),
                    'occurred_at' => $date->toDateString(),
                    'description' => 'Monthly salary',
                    'amount' => $faker->numberBetween(10000000, 16000000),
                    'type' => 'income',
                    'notes' => $faker->sentence(),
                ];
            }

            if ($date->day % 7 === 0) {
                $transactions[] = [
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $incomeCategories->random(),
                    'occurred_at' => $date->toDateString(),
                    'description' => 'Freelance project',
                    'amount' => $faker->numberBetween(1500000, 3500000),
                    'type' => 'income',
                    'notes' => $faker->sentence(),
                ];
            }

            $dailyExpenses = rand(1, 3);
            for ($i = 0; $i < $dailyExpenses; $i++) {
                $transactions[] = [
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $expenseCategories->random(),
                    'occurred_at' => $date->toDateString(),
                    'description' => $faker->randomElement([
                        'Coffee with friends',
                        'Lunch',
                        'Online shopping',
                        'Ride-hailing',
                        'Monthly bill payment',
                        'Groceries run',
                        'Movie night',
                        'Streaming subscription',
                    ]),
                    'amount' => $faker->numberBetween(30000, 750000),
                    'type' => 'expense',
                    'notes' => $faker->sentence(),
                ];
            }
        }

        foreach (collect($transactions)->shuffle()->take(80) as $data) {
            Transaction::create($data);
        }
    }
}
