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
        return view('wallets.index', [
            'wallets' => Wallet::withSum(['transactions as income_sum' => function ($query) {
                    $query->where('type', 'income');
                }], 'amount')
                ->withSum(['transactions as expense_sum' => function ($query) {
                    $query->where('type', 'expense');
                }], 'amount')
                ->orderBy('name')
                ->get()
                ->map(function (Wallet $wallet) {
                    $wallet->income_sum = (float) ($wallet->income_sum ?? 0);
                    $wallet->expense_sum = (float) ($wallet->expense_sum ?? 0);
                    $wallet->net_flow = $wallet->income_sum - $wallet->expense_sum;

                    return $wallet;
                }),
            'wallet' => new Wallet(['type' => 'cash']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWallet($request);

        Wallet::create($data);

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil ditambahkan.');
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $data = $this->validateWallet($request, $wallet);

        $wallet->update($data);

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil diperbarui.');
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
