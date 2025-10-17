<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $currentMonthTransactions = Transaction::with(['wallet', 'category'])
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->get();

        $totalIncome = $currentMonthTransactions->where('type', 'income')->sum('amount');
        $totalExpense = $currentMonthTransactions->where('type', 'expense')->sum('amount');
        $net = $totalIncome - $totalExpense;

        $walletBalances = Wallet::with(['transactions' => function ($query) {
            $query->orderBy('occurred_at');
        }])->get()->map(function (Wallet $wallet) {
            $balance = $wallet->starting_balance + $wallet->transactions
                ->reduce(function ($carry, $transaction) {
                    return $transaction->type === 'income'
                        ? $carry + $transaction->amount
                        : $carry - $transaction->amount;
                }, 0);

            return [
                'wallet' => $wallet,
                'balance' => $balance,
            ];
        });

        $months = collect(range(0, 5))->map(function ($offset) use ($today) {
            return $today->copy()->subMonths($offset)->startOfMonth();
        })->sort()->values();

        $monthlySeries = $months->map(function (Carbon $month) {
            $start = $month->copy();
            $end = $month->copy()->endOfMonth();

            $transactions = Transaction::whereBetween('occurred_at', [$start, $end])->get();

            return [
                'label' => $month->isoFormat('MMM YYYY'),
                'income' => $transactions->where('type', 'income')->sum('amount'),
                'expense' => $transactions->where('type', 'expense')->sum('amount'),
            ];
        });

        $expenseByCategory = $currentMonthTransactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = optional($items->first()->category);
                return [
                    'name' => $category?->name ?? 'Uncategorized',
                    'color' => $category?->color,
                    'amount' => $items->sum('amount'),
                ];
            })->sortByDesc('amount');

        $topCategories = $expenseByCategory->take(6);
        $otherTotal = $expenseByCategory->slice(6)->sum('amount');
        if ($otherTotal > 0) {
            $topCategories->push([
                'name' => 'Others',
                'color' => '#CBD5F5',
                'amount' => $otherTotal,
            ]);
        }

        $recentTransactions = Transaction::with(['wallet', 'category'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('dashboard.index', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $net,
            'walletBalances' => $walletBalances,
            'monthlySeries' => $monthlySeries,
            'expenseByCategory' => $topCategories,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
