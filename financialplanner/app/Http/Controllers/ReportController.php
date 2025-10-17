<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $start = $selectedMonth->copy();
        $end = $selectedMonth->copy()->endOfMonth();

        $transactions = Transaction::with(['category', 'wallet'])
            ->whereBetween('occurred_at', [$start, $end])
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $weeklySummary = $transactions->groupBy(function ($transaction) {
            return Carbon::parse($transaction->occurred_at)->startOfWeek()->isoFormat('DD MMM');
        })->map(function ($items) {
            return [
                'income' => $items->where('type', 'income')->sum('amount'),
                'expense' => $items->where('type', 'expense')->sum('amount'),
            ];
        });

        $categoryBreakdown = $transactions->where('type', 'expense')
            ->groupBy('category_id')
            ->map(function ($items) {
                $category = optional($items->first()->category);
                return [
                    'category' => $category,
                    'amount' => $items->sum('amount'),
                ];
            })->sortByDesc('amount');

        $walletBreakdown = $transactions->groupBy('wallet_id')->map(function ($items) {
            $wallet = optional($items->first()->wallet);
            return [
                'wallet' => $wallet,
                'income' => $items->where('type', 'income')->sum('amount'),
                'expense' => $items->where('type', 'expense')->sum('amount'),
            ];
        });

        return view('reports.index', [
            'selectedMonth' => $selectedMonth,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'weeklySummary' => $weeklySummary,
            'categoryBreakdown' => $categoryBreakdown,
            'walletBreakdown' => $walletBreakdown,
            'availableMonths' => $this->availableMonths(),
        ]);
    }

    protected function availableMonths()
    {
        return Transaction::select('occurred_at')
            ->orderByDesc('occurred_at')
            ->get()
            ->map(fn ($transaction) => Carbon::parse($transaction->occurred_at)->startOfMonth())
            ->unique(fn ($date) => $date->format('Y-m'))
            ->sortDesc()
            ->values();
    }
}
