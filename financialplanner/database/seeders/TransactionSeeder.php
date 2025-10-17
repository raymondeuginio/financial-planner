<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

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

        $salaryCategory = $incomeCategories['Gaji Pokok'] ?? $incomeCategories->first();
        $bonusCategory = $incomeCategories['Bonus Kinerja'] ?? $incomeCategories->first();
        $refundCategory = $incomeCategories['Pengembalian Dana'] ?? $incomeCategories->first();

        $salaryAmounts = [12500000, 12750000, 13000000];
        $bonusAmounts = [1500000, 2000000, 2500000, 3000000];
        $refundAmounts = [250000, 300000, 400000, 500000];

        $period = CarbonPeriod::create($start, '1 month', $end);
        $index = 0;
        foreach ($period as $month) {
            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                'category_id' => $salaryCategory->id,
                'occurred_at' => $month->copy()->addDays(1),
                'description' => 'Gaji Bulanan',
                'amount' => $salaryAmounts[$index % count($salaryAmounts)],
                'type' => 'income',
                'notes' => 'Transfer gaji dari perusahaan',
            ]);

            if ($faker->boolean(60)) {
                Transaction::create([
                    'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                    'category_id' => $bonusCategory->id,
                    'occurred_at' => $month->copy()->addDays($faker->numberBetween(5, 18)),
                    'description' => 'Bonus Kinerja',
                    'amount' => Arr::random($bonusAmounts),
                    'type' => 'income',
                    'notes' => 'Insentif kuartalan',
                ]);
            }

            if ($faker->boolean(40)) {
                Transaction::create([
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $refundCategory->id,
                    'occurred_at' => $month->copy()->addDays($faker->numberBetween(10, 24)),
                    'description' => 'Reimburse',
                    'amount' => Arr::random($refundAmounts),
                    'type' => 'income',
                    'notes' => 'Penggantian biaya kantor',
                ]);
            }

            $index++;
        }

        $expenseTemplates = [
            'Makanan & Minuman' => [
                ['Sarapan di kafe', 45000],
                ['Belanja pasar mingguan', 185000],
                ['Makan malam keluarga', 275000],
                ['Ngopi bareng teman', 60000],
            ],
            'Transportasi' => [
                ['Isi bensin motor', 120000],
                ['Taksi online ke kantor', 55000],
                ['Parkir harian', 25000],
                ['Langganan KRL', 150000],
            ],
            'Tagihan Rumah' => [
                ['Tagihan listrik PLN', 450000],
                ['Tagihan internet fiber', 375000],
                ['Pembayaran PDAM', 185000],
                ['Iuran kebersihan', 75000],
            ],
            'Belanja Rumah Tangga' => [
                ['Belanja kebutuhan dapur', 320000],
                ['Perlengkapan kebersihan', 98000],
                ['Isi ulang gas 12kg', 225000],
                ['Perlengkapan mandi', 87000],
            ],
            'Hiburan' => [
                ['Langganan streaming', 169000],
                ['Bioskop akhir pekan', 120000],
                ['Karaoke bersama', 210000],
                ['Hangout malam minggu', 150000],
            ],
        ];

        for ($i = 0; $i < 70; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));
            $category = $expenseCategories->random();
            $wallet = $wallets->random();
            $options = $expenseTemplates[$category->name] ?? [['Pengeluaran harian', 95000]];
            [$description, $amount] = Arr::random($options);

            Transaction::create([
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'occurred_at' => $date,
                'description' => $description,
                'amount' => $amount,
                'type' => 'expense',
                'notes' => $faker->optional()->sentence(4),
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));

            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                'category_id' => $bonusCategory->id,
                'occurred_at' => $date,
                'description' => 'Top up dompet digital',
                'amount' => Arr::random([200000, 250000, 300000, 350000]),
                'type' => 'income',
                'notes' => 'Pemindahan dana untuk kebutuhan harian',
            ]);

            $shoppingCategory = $expenseCategories->firstWhere('name', 'Belanja Rumah Tangga') ?? $expenseCategories->first();

            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'ewallet')->id ?? $wallets->last()->id,
                'category_id' => $shoppingCategory->id,
                'occurred_at' => $date,
                'description' => 'Pembelian marketplace',
                'amount' => Arr::random([135000, 185000, 225000, 265000]),
                'type' => 'expense',
                'notes' => 'Pesanan kebutuhan rumah',
            ]);
        }
    }
}
