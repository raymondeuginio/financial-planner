<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $monthInput = $request->input('month');
        if ($monthInput && preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
            $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } else {
            $selectedMonth = Carbon::now()->startOfMonth();
        }
        $endOfMonth = $selectedMonth->copy()->endOfMonth();

        $monthlyRangeStart = $selectedMonth->copy()->subMonths(5);
        $monthlySeries = Transaction::selectRaw('DATE_FORMAT(occurred_at, "%Y-%m") as ym, type, SUM(amount) as total')
            ->where('occurred_at', '>=', $monthlyRangeStart)
            ->groupBy('ym', 'type')
            ->orderBy('ym')
            ->get()
            ->groupBy('ym');

        $months = collect(range(0, 5))->map(fn ($i) => $monthlyRangeStart->copy()->addMonths($i));
        $incomeSeries = [];
        $expenseSeries = [];
        foreach ($months as $month) {
            $key = $month->format('Y-m');
            $group = $monthlySeries->get($key);
            $incomeSeries[] = (float) optional($group?->firstWhere('type', 'income'))->total ?? 0;
            $expenseSeries[] = (float) optional($group?->firstWhere('type', 'expense'))->total ?? 0;
        }

        $weeklyData = Transaction::selectRaw('YEARWEEK(occurred_at, 1) as year_week, MIN(occurred_at) as week_start, MAX(occurred_at) as week_end, SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total, SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->whereBetween('occurred_at', [$selectedMonth, $endOfMonth])
            ->groupBy('year_week')
            ->orderBy('week_start')
            ->get()
            ->map(function ($row) {
                $start = Carbon::parse($row->week_start);
                return [
                    'label' => $start->isoFormat('MMM D'),
                    'income' => (float) $row->income_total,
                    'expense' => (float) $row->expense_total,
                ];
            });

        $categoryBreakdown = Category::withSum(['transactions as expense_total' => function ($query) use ($selectedMonth, $endOfMonth) {
                $query->where('type', 'expense')->whereBetween('occurred_at', [$selectedMonth, $endOfMonth]);
            }], 'amount')
            ->where('type', 'expense')
            ->get()
            ->sortByDesc(fn ($category) => (float) $category->expense_total)
            ->values();

        $walletBreakdown = Wallet::withSum(['transactions as income_total' => function ($query) use ($selectedMonth, $endOfMonth) {
                $query->where('type', 'income')->whereBetween('occurred_at', [$selectedMonth, $endOfMonth]);
            }], 'amount')
            ->withSum(['transactions as expense_total' => function ($query) use ($selectedMonth, $endOfMonth) {
                $query->where('type', 'expense')->whereBetween('occurred_at', [$selectedMonth, $endOfMonth]);
            }], 'amount')
            ->orderBy('name')
            ->get();

        return view('reports.index', [
            'selectedMonth' => $selectedMonth,
            'months' => $months,
            'incomeSeries' => $incomeSeries,
            'expenseSeries' => $expenseSeries,
            'weeklyData' => $weeklyData,
            'categoryBreakdown' => $categoryBreakdown,
            'walletBreakdown' => $walletBreakdown,
        ]);
    }
}
