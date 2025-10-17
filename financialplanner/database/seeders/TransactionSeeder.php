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
                'category_id' => $incomeCategories['Gaji']->id ?? $incomeCategories->first()->id,
                'occurred_at' => $month->copy()->addDays(1),
                'description' => 'Gaji Bulanan',
                'amount' => 15000000 + (($month->month % 3) * 250000),
                'type' => 'income',
                'notes' => 'Ditransfer otomatis dari perusahaan',
            ]);

            if ($faker->boolean(60)) {
                Transaction::create([
                    'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                    'category_id' => $incomeCategories['Bonus']->id ?? $incomeCategories->first()->id,
                    'occurred_at' => $month->copy()->addDays(random_int(5, 20)),
                    'description' => 'Bonus Kinerja',
                    'amount' => collect([2000000, 2250000, 2500000, 3000000, 3500000])->random(),
                    'type' => 'income',
                    'notes' => 'Insentif kinerja triwulan',
                ]);
            }

            if ($faker->boolean(40)) {
                Transaction::create([
                    'wallet_id' => $wallets->random()->id,
                    'category_id' => $incomeCategories['Pengembalian']->id ?? $incomeCategories->first()->id,
                    'occurred_at' => $month->copy()->addDays(random_int(10, 25)),
                    'description' => 'Pengembalian Dana',
                    'amount' => collect([250000, 300000, 450000, 600000, 750000])->random(),
                    'type' => 'income',
                    'notes' => 'Penggantian biaya sebelumnya',
                ]);
            }
        }

        $expenseNotes = [
            'Menggunakan pembayaran non tunai',
            'Memanfaatkan promo akhir pekan',
            'Tercatat otomatis di aplikasi',
            'Struk disimpan untuk arsip',
            'Pembayaran terjadwal setiap bulan',
        ];

        // Daily expenses sprinkled across wallets and categories
        for ($i = 0; $i < 65; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));
            $category = $expenseCategories->random();
            $wallet = $wallets->random();

            $description = match ($category->name) {
                'Makan & Minum' => $faker->randomElement(['Makan siang bersama rekan', 'Ngopi sore', 'Makan malam keluarga', 'Pesan makan daring']),
                'Transportasi' => $faker->randomElement(['Naik ojek online', 'Isi bensin', 'Beli kartu komuter', 'Bayar parkir']),
                'Tagihan Rumah Tangga' => $faker->randomElement(['Bayar listrik', 'Langganan internet', 'Tagihan air PDAM', 'Isi pulsa listrik']),
                'Belanja Pribadi' => $faker->randomElement(['Belanja bahan dapur', 'Pesanan marketplace', 'Beli pakaian', 'Perlengkapan rumah']),
                'Hiburan' => $faker->randomElement(['Nonton bioskop', 'Langganan streaming', 'Tiket konser', 'Hangout akhir pekan']),
                default => $faker->sentence(),
            };

            Transaction::create([
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'occurred_at' => $date,
                'description' => $description,
                'amount' => $this->randomExpenseAmount($category->name),
                'type' => 'expense',
                'notes' => $faker->boolean(40) ? collect($expenseNotes)->random() : null,
            ]);
        }

        // Occasional top-ups between wallets
        for ($i = 0; $i < 8; $i++) {
            $date = Carbon::parse($faker->dateTimeBetween($start, $end));
            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'bank')->id ?? $wallets->first()->id,
                'category_id' => $incomeCategories['Bonus']->id ?? $incomeCategories->first()->id,
                'occurred_at' => $date,
                'description' => 'Pindah dana ke dompet',
                'amount' => collect([250000, 300000, 350000, 400000, 450000, 500000])->random(),
                'type' => 'income',
                'notes' => 'Transfer dari tabungan',
            ]);

            Transaction::create([
                'wallet_id' => $wallets->firstWhere('type', 'ewallet')->id ?? $wallets->last()->id,
                'category_id' => $expenseCategories->firstWhere('name', 'Belanja Pribadi')->id ?? $expenseCategories->first()->id,
                'occurred_at' => $date,
                'description' => 'Belanja promo daring',
                'amount' => collect([150000, 175000, 225000, 275000, 325000, 375000, 425000])->random(),
                'type' => 'expense',
                'notes' => 'Pesanan menggunakan voucher',
            ]);
        }
    }

    protected function randomExpenseAmount(string $categoryName): int
    {
        $choices = match ($categoryName) {
            'Makan & Minum' => [35000, 55000, 75000, 95000, 125000, 150000, 185000],
            'Transportasi' => [20000, 30000, 45000, 60000, 75000, 90000, 120000],
            'Tagihan Rumah Tangga' => [150000, 250000, 350000, 450000, 550000, 750000, 900000],
            'Belanja Pribadi' => [85000, 120000, 160000, 210000, 260000, 320000, 420000],
            'Hiburan' => [65000, 85000, 110000, 150000, 185000, 230000, 300000],
            default => [50000, 75000, 100000, 125000, 150000, 200000],
        };

        return collect($choices)->random();
    }
}
