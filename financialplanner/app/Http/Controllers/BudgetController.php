<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $selectedMonthInput = $request->input('month');
        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $selectedMonthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $selectedMonth = Carbon::now()->startOfMonth();
        }

        $selectedYear = (int) $selectedMonth->format('Y');
        $monthGrid = collect(range(1, 12))->map(fn ($month) => Carbon::create($selectedYear, $month, 1));

        $budgets = Budget::with('category')
            ->whereDate('month', $selectedMonth->toDateString())
            ->orderBy('category_id')
            ->get();

        $expensesByCategory = Transaction::selectRaw('category_id, SUM(amount) as total')
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$selectedMonth->copy()->startOfMonth(), $selectedMonth->copy()->endOfMonth()])
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $budgets->transform(function (Budget $budget) use ($expensesByCategory) {
            $totalSpent = (float) ($expensesByCategory[$budget->category_id]->total ?? 0);
            $budget->spent_amount = $totalSpent;
            $budget->remaining_amount = (float) $budget->amount - $totalSpent;

            return $budget;
        });

        $monthlyTransactions = Transaction::with(['category', 'wallet'])
            ->whereBetween('occurred_at', [$selectedMonth->copy()->startOfMonth(), $selectedMonth->copy()->endOfMonth()])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => Category::where('type', 'expense')->orderBy('name')->get(),
            'budget' => new Budget(['month' => $selectedMonth, 'amount' => 0]),
            'selectedMonth' => $selectedMonth,
            'monthGrid' => $monthGrid,
            'monthlyTransactions' => $monthlyTransactions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateBudget($request);
        $month = first_day_of_month($data['month']);
        $data['month'] = $month;

        Budget::updateOrCreate(
            ['category_id' => $data['category_id'], 'month' => $data['month']],
            ['amount' => $data['amount']]
        );

        return redirect()->route('budgets.index', ['month' => Carbon::parse($month)->format('Y-m')])
            ->with('status', 'Anggaran berhasil disimpan.');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $data = $this->validateBudget($request, $budget);
        $month = first_day_of_month($data['month']);
        $data['month'] = $month;

        $budget->update($data);

        return redirect()->route('budgets.index', ['month' => Carbon::parse($month)->format('Y-m')])
            ->with('status', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $month = $budget->month?->format('Y-m');

        $budget->delete();

        return redirect()->route('budgets.index', $month ? ['month' => $month] : [])
            ->with('status', 'Anggaran berhasil dihapus.');
    }

    protected function validateBudget(Request $request, ?Budget $budget = null): array
    {
        return $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'expense')],
            'month' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
