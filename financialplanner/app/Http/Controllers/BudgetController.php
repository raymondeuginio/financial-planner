<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $monthInput = $request->input('month', now()->format('Y-m'));
        try {
            $selectedMonth = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        } catch (\Exception $e) {
            $selectedMonth = now()->startOfMonth();
        }

        $startOfMonth = $selectedMonth->copy();
        $endOfMonth = $selectedMonth->copy()->endOfMonth();

        $budgets = Budget::with('category')
            ->whereDate('month', $startOfMonth)
            ->orderBy('category_id')
            ->get();

        $spent = Transaction::select('category_id', DB::raw('SUM(amount) as total'))
            ->where('type', 'expense')
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $budgets->transform(function (Budget $budget) use ($spent) {
            $totalSpent = (float) ($spent[$budget->category_id] ?? 0);
            $budget->spent_amount = $totalSpent;
            $budget->remaining_amount = (float) $budget->amount - $totalSpent;

            return $budget;
        });

        $transactions = Transaction::with(['category', 'wallet'])
            ->whereBetween('occurred_at', [$startOfMonth, $endOfMonth])
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $summary = [
            'income' => (float) $transactions->where('type', 'income')->sum('amount'),
            'expense' => (float) $transactions->where('type', 'expense')->sum('amount'),
        ];
        $summary['net'] = $summary['income'] - $summary['expense'];

        $months = collect(range(0, 11))
            ->map(fn ($i) => $selectedMonth->copy()->startOfYear()->addMonths($i));

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => Category::where('type', 'expense')->orderBy('name')->get(),
            'budget' => new Budget(['month' => $selectedMonth, 'amount' => 0]),
            'selectedMonth' => $selectedMonth,
            'months' => $months,
            'transactions' => $transactions,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateBudget($request);
        $data['month'] = first_day_of_month($data['month']);
        $redirectMonth = Carbon::parse($data['month'])->format('Y-m');

        Budget::updateOrCreate(
            ['category_id' => $data['category_id'], 'month' => $data['month']],
            ['amount' => $data['amount']]
        );

        return redirect()->route('budgets.index', ['month' => $redirectMonth])->with('status', 'Anggaran berhasil disimpan.');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $data = $this->validateBudget($request, $budget);
        $data['month'] = first_day_of_month($data['month']);
        $redirectMonth = Carbon::parse($data['month'])->format('Y-m');

        $budget->update($data);

        return redirect()->route('budgets.index', ['month' => $redirectMonth])->with('status', 'Anggaran berhasil diperbarui.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return redirect()->route('budgets.index', ['month' => $budget->month->format('Y-m')])->with('status', 'Anggaran berhasil dihapus.');
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
