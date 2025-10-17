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
            'wallets' => Wallet::orderBy('name')->get(),
            'wallet' => new Wallet(['type' => 'cash', 'starting_balance' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateWallet($request);

        Wallet::create($data);

        return redirect()->route('wallets.index')->with('status', 'Wallet created.');
    }

    public function update(Request $request, Wallet $wallet): RedirectResponse
    {
        $data = $this->validateWallet($request, $wallet);

        $wallet->update($data);

        return redirect()->route('wallets.index')->with('status', 'Wallet updated.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Wallet deleted.');
    }

    protected function validateWallet(Request $request, ?Wallet $wallet = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank', 'ewallet'])],
            'starting_balance' => ['required', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);
    }
}
