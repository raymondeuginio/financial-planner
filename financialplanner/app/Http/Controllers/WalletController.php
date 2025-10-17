<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletController extends Controller
{
    public function index(): View
    {
        $wallets = Wallet::query()
            ->withSum(['transactions as total_income' => function ($query) {
                $query->where('type', 'income');
            }], 'amount')
            ->withSum(['transactions as total_expense' => function ($query) {
                $query->where('type', 'expense');
            }], 'amount')
            ->orderBy('name')
            ->get()
            ->map(function (Wallet $wallet) {
                $wallet->total_income = (float) ($wallet->total_income ?? 0);
                $wallet->total_expense = (float) ($wallet->total_expense ?? 0);
                $wallet->current_balance = $wallet->total_income - $wallet->total_expense;

                return $wallet;
            });

        return view('wallets.index', [
            'wallets' => $wallets,
            'wallet' => new Wallet(['type' => 'cash']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWallet($request);

        Wallet::create($data);

        return redirect()->route('wallets.index')->with('status', 'Dompet baru berhasil disimpan.');
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $data = $this->validateWallet($request, $wallet);

        $wallet->update($data);

        return redirect()->route('wallets.index')->with('status', 'Informasi dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil dihapus.');
    }

    protected function validateWallet(Request $request, ?Wallet $wallet = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank', 'ewallet'])],
            'color' => ['nullable', 'string', 'max:7'],
        ]);
    }
}
