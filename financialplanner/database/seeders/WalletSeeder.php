<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = [
            ['name' => 'Cash', 'type' => 'cash', 'starting_balance' => 1500000, 'color' => '#f97316'],
            ['name' => 'BCA', 'type' => 'bank', 'starting_balance' => 7500000, 'color' => '#2563eb'],
            ['name' => 'GoPay', 'type' => 'ewallet', 'starting_balance' => 500000, 'color' => '#10b981'],
        ];

        foreach ($wallets as $wallet) {
            Wallet::updateOrCreate(['name' => $wallet['name']], $wallet);
        }
    }
}
