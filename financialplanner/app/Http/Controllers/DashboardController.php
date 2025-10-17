<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $monthlyTotals = Transaction::select('type', DB::raw('SUM(amount) as total'))
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->groupBy('type')
            ->pluck('total', 'type');

        $totalIncome = (float) ($monthlyTotals['income'] ?? 0);
        $totalExpense = (float) ($monthlyTotals['expense'] ?? 0);
        $net = $totalIncome - $totalExpense;

        $wallets = Wallet::withSum(['transactions as income_sum' => function ($query) {
                $query->where('type', 'income');
            }], 'amount')
            ->withSum(['transactions as expense_sum' => function ($query) {
                $query->where('type', 'expense');
            }], 'amount')
            ->get()
            ->map(function (Wallet $wallet) {
                $income = (float) ($wallet->income_sum ?? 0);
                $expense = (float) ($wallet->expense_sum ?? 0);
                $wallet->current_balance = (float) $wallet->starting_balance + $income - $expense;

                return $wallet;
            });

        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();
        $monthlySeries = Transaction::selectRaw('DATE_FORMAT(occurred_at, "%Y-%m") as ym, type, SUM(amount) as total')
            ->where('occurred_at', '>=', $sixMonthsAgo)
            ->groupBy('ym', 'type')
            ->orderBy('ym')
            ->get()
            ->groupBy('ym');

        $months = collect(range(0, 5))->map(fn ($i) => $sixMonthsAgo->copy()->addMonths($i))->map(fn ($date) => $date->format('Y-m'));

        $incomeSeries = [];
        $expenseSeries = [];
        foreach ($months as $ym) {
            $group = $monthlySeries->get($ym);
            $incomeSeries[] = (float) optional($group?->firstWhere('type', 'income'))->total ?? 0;
            $expenseSeries[] = (float) optional($group?->firstWhere('type', 'expense'))->total ?? 0;
        }

        $expenseByCategory = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->with('category')
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get();

        $topCategories = $expenseByCategory->take(6);
        $otherTotal = max(0, (float) $expenseByCategory->skip(6)->sum('total'));

        $recentTransactions = Transaction::with(['wallet', 'category'])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('dashboard.index', [
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'net' => $net,
            'wallets' => $wallets,
            'months' => $months->map(fn ($ym) => Carbon::createFromFormat('Y-m', $ym)->isoFormat('MMM YYYY')),
            'incomeSeries' => $incomeSeries,
            'expenseSeries' => $expenseSeries,
            'expenseCategories' => $topCategories,
            'expenseOthersTotal' => $otherTotal,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
