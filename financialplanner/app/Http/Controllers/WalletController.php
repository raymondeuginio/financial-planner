<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function index()
    {
        return view('wallets.index', [
            'wallets' => Wallet::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:cash,bank,ewallet'],
            'starting_balance' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        Wallet::create($data);

        return redirect()->route('wallets.index')->with('success', 'Wallet created.');
    }

    public function update(Request $request, Wallet $wallet)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:cash,bank,ewallet'],
            'starting_balance' => ['nullable', 'numeric', 'min:0'],
            'color' => ['nullable', 'string', 'max:7'],
        ]);

        $wallet->update($data);

        return redirect()->route('wallets.index')->with('success', 'Wallet updated.');
    }

    public function destroy(Wallet $wallet)
    {
        $wallet->delete();

        return redirect()->route('wallets.index')->with('success', 'Wallet deleted.');
    }
}
