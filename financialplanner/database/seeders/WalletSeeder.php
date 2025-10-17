<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = [
            ['name' => 'Cash', 'type' => 'cash', 'starting_balance' => 1500000, 'color' => '#F97316'],
            ['name' => 'BCA', 'type' => 'bank', 'starting_balance' => 5000000, 'color' => '#2563EB'],
            ['name' => 'GoPay', 'type' => 'ewallet', 'starting_balance' => 750000, 'color' => '#10B981'],
        ];

        foreach ($wallets as $wallet) {
            Wallet::updateOrCreate(['name' => $wallet['name']], $wallet);
        }
    }
}
