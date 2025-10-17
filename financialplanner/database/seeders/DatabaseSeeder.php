<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WalletSeeder::class,
            CategorySeeder::class,
            BudgetSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
