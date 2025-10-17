<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $wallets = [
            ['name' => 'Dompet Tunai', 'type' => 'cash', 'color' => '#f97316'],
            ['name' => 'Rekening BCA', 'type' => 'bank', 'color' => '#2563eb'],
            ['name' => 'GoPay', 'type' => 'ewallet', 'color' => '#10b981'],
        ];

        foreach ($wallets as $wallet) {
            Wallet::updateOrCreate(['name' => $wallet['name']], $wallet);
        }
    }
}
