<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $wallets = Wallet::all();
        $incomeCategories = Category::where('type', 'income')->get()->keyBy('name');
        $expenseCategories = Category::where('type', 'expense')->get();

        if ($wallets->isEmpty() || $incomeCategories->isEmpty() || $expenseCategories->isEmpty()) {
            return;
        }

        $start = now()->subMonths(3)->startOfMonth();
        $end = now()->endOfMonth();

        // Recurring monthly incomes
        $period = CarbonPeriod::create($start, '1 month', $end);
        foreach ($period as $month) {
            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                'category_id' => $incomeCategories['Salary']->id ?? $incomeCategories->first()->id,
                'occurred_at' => $month->copy()->addDays(1),
                'description' => 'Monthly Salary',
                'amount' => 15000000 + random_int(-1000000, 1500000),
                'type' => 'income',
                'notes' => 'Deposited automatically',
            ]);

            if ($faker->boolean(60)) {
                Transaction::create([
                    'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                    'category_id' => $incomeCategories['Bonus']->id ?? $incomeCategories->first()->id,
                    'occurred_at' => $month->copy()->addDays(random_int(5, 20)),
                    'description' => 'Performance Bonus',
                    'amount' => random_int(1000000, 4000000),
                    'type' => 'income',
                    'notes' => 'Quarter incentive',
                ]);
            }

            if ($faker->boolean(40)) {
                Transaction::create([
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $incomeCategories['Refund']->id ?? $incomeCategories->first()->id,
                    'occurred_at' => $month->copy()->addDays(random_int(10, 25)),
                    'description' => 'Refund',
                    'amount' => random_int(200000, 800000),
                    'type' => 'income',
                    'notes' => 'Expense reimbursement',
                ]);
            }
        }

        // Daily expenses sprinkled across wallets and categories
        for ($i = 0; $i < 65; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));
            $category = $expenseCategories->random();
            $wallet = $wallets->random();

            $description = match ($category->name) {
                'Food' => $faker->randomElement(['Lunch with friends', 'Coffee break', 'Family dinner', 'Takeaway meal']),
                'Transport' => $faker->randomElement(['Grab ride', 'Fuel refill', 'Commuter pass', 'Parking fee']),
                'Bills' => $faker->randomElement(['Electricity bill', 'Internet subscription', 'Water utility', 'Phone top-up']),
                'Shopping' => $faker->randomElement(['Groceries run', 'Online order', 'Clothing purchase', 'Household supplies']),
                'Entertainment' => $faker->randomElement(['Movie night', 'Streaming subscription', 'Concert ticket', 'Weekend outing']),
                default => $faker->sentence(3),
            };

            Transaction::create([
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'occurred_at' => $date,
                'description' => $description,
                'amount' => $this->randomExpenseAmount($category->name),
                'type' => 'expense',
                'notes' => $faker->optional()->sentence(),
            ]);
        }

        // Occasional top-ups between wallets
        for ($i = 0; $i < 8; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));
            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                'category_id' => $incomeCategories['Bonus']->id ?? $incomeCategories->first()->id,
                'occurred_at' => $date,
                'description' => 'Wallet top-up',
                'amount' => random_int(200000, 600000),
                'type' => 'income',
                'notes' => 'Moved from savings',
            ]);

            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'ewallet')->id ?? $wallets->last()->id,
                'category_id' => $expenseCategories->firstWhere('name', 'Shopping')->id ?? $expenseCategories->first()->id,
                'occurred_at' => $date,
                'description' => 'Marketplace order',
                'amount' => random_int(150000, 450000),
                'type' => 'expense',
                'notes' => 'Promo purchase',
            ]);
        }
    }

    protected function randomExpenseAmount(string $categoryName): int
    {
        return match ($categoryName) {
            'Food' => random_int(30000, 180000),
            'Transport' => random_int(20000, 120000),
            'Bills' => random_int(150000, 900000),
            'Shopping' => random_int(75000, 600000),
            'Entertainment' => random_int(50000, 350000),
            default => random_int(25000, 200000),
        };
    }
}
