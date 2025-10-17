<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $budgets = Budget::with('category')->orderByDesc('month')->orderBy('category_id')->get();

        $budgetSummaries = $budgets->map(function (Budget $budget) {
            $start = Carbon::parse($budget->month)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $spent = Transaction::where('category_id', $budget->category_id)
                ->where('type', 'expense')
                ->whereBetween('occurred_at', [$start, $end])
                ->sum('amount');

            return [
                'budget' => $budget,
                'spent' => $spent,
                'remaining' => max($budget->amount - $spent, 0),
            ];
        });

        return view('budgets.index', [
            'budgetSummaries' => $budgetSummaries,
            'categories' => Category::where('type', 'expense')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBudget($request);
        Budget::create($data);

        return redirect()->route('budgets.index')->with('success', 'Budget created.');
    }

    public function update(Request $request, Budget $budget)
    {
        $data = $this->validateBudget($request);
        $budget->update($data);

        return redirect()->route('budgets.index')->with('success', 'Budget updated.');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget deleted.');
    }

    protected function validateBudget(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'category_id' => ['required', 'exists:categories,id'],
            'month' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('category_id')) {
                $category = Category::find($request->input('category_id'));
                if ($category && $category->type !== 'expense') {
                    $validator->errors()->add('category_id', 'Budgets can only be assigned to expense categories.');
                }
            }
        });

        $data = $validator->validate();
        $data['month'] = first_day_of_month($data['month']);

        return $data;
    }
}
