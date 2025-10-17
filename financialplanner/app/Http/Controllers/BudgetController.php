<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(): View
    {
        $budgets = Budget::with('category')
            ->orderByDesc('month')
            ->orderBy('category_id')
            ->get();

        $spent = collect();
        if ($budgets->isNotEmpty()) {
            $startMonth = $budgets->min('month')->copy()->startOfMonth();
            $endMonth = $budgets->max('month')->copy()->endOfMonth();
            $categoryIds = $budgets->pluck('category_id')->unique()->values();

            $spent = Transaction::selectRaw('category_id, DATE_FORMAT(occurred_at, "%Y-%m-01") as month_key, SUM(amount) as total')
                ->where('type', 'expense')
                ->whereBetween('occurred_at', [$startMonth, $endMonth])
                ->whereIn('category_id', $categoryIds)
                ->groupBy('category_id', 'month_key')
                ->get()
                ->keyBy(fn ($row) => $row->category_id . '|' . $row->month_key);
        }

        $budgets->transform(function (Budget $budget) use ($spent) {
            $key = $budget->category_id . '|' . $budget->month->toDateString();
            $totalSpent = (float) ($spent[$key]->total ?? 0);
            $budget->spent_amount = $totalSpent;
            $budget->remaining_amount = (float) $budget->amount - $totalSpent;

            return $budget;
        });

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => Category::where('type', 'expense')->orderBy('name')->get(),
            'budget' => new Budget(['month' => now()->startOfMonth(), 'amount' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateBudget($request);
        $data['month'] = first_day_of_month($data['month']);

        Budget::updateOrCreate(
            ['category_id' => $data['category_id'], 'month' => $data['month']],
            ['amount' => $data['amount']]
        );

        return redirect()->route('budgets.index')->with('status', 'Budget saved.');
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $data = $this->validateBudget($request, $budget);
        $data['month'] = first_day_of_month($data['month']);

        $budget->update($data);

        return redirect()->route('budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
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
