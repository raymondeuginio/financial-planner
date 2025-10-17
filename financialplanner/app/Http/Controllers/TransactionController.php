<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'type', 'wallet_id', 'category_id', 'search']);

        $query = Transaction::with(['wallet', 'category'])->orderByDesc('occurred_at')->orderByDesc('id');

        if ($filters['start_date'] ?? null) {
            $query->whereDate('occurred_at', '>=', $filters['start_date']);
        }

        if ($filters['end_date'] ?? null) {
            $query->whereDate('occurred_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('description', 'like', '%'.$filters['search'].'%')
                    ->orWhere('notes', 'like', '%'.$filters['search'].'%');
            });
        }

        $transactions = $query->paginate(10)->appends($filters);

        return view('transactions.index', [
            'transactions' => $transactions,
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('transactions.form', [
            'transaction' => new Transaction(),
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTransaction($request);
        Transaction::create($data);

        return redirect()->route('transactions.index')->with('success', 'Transaction recorded.');
    }

    public function edit(Transaction $transaction)
    {
        return view('transactions.form', [
            'transaction' => $transaction,
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $data = $this->validateTransaction($request, $transaction);
        $transaction->update($data);

        return redirect()->route('transactions.index')->with('success', 'Transaction updated.');
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaction removed.');
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'type', 'wallet_id', 'category_id', 'search']);

        $query = Transaction::with(['wallet', 'category'])->orderBy('occurred_at');

        if ($filters['start_date'] ?? null) {
            $query->whereDate('occurred_at', '>=', $filters['start_date']);
        }

        if ($filters['end_date'] ?? null) {
            $query->whereDate('occurred_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('description', 'like', '%'.$filters['search'].'%')
                    ->orWhere('notes', 'like', '%'.$filters['search'].'%');
            });
        }

        $filename = 'transactions_'.Carbon::now()->format('Ymd_His').'.csv';

        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Type', 'Category', 'Wallet', 'Description', 'Amount', 'Notes']);

            $query->chunk(100, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->occurred_at->toDateString(),
                        ucfirst($row->type),
                        optional($row->category)->name,
                        optional($row->wallet)->name,
                        $row->description,
                        $row->amount,
                        $row->notes,
                    ]);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    protected function validateTransaction(Request $request, ?Transaction $transaction = null): array
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => ['required', 'exists:wallets,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'occurred_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:income,expense'],
            'notes' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->filled('category_id') && $request->filled('type')) {
                $category = Category::find($request->input('category_id'));
                if ($category && $category->type !== $request->input('type')) {
                    $validator->errors()->add('category_id', 'Category type mismatch with transaction type.');
                }
            }
        });

        return $validator->validate();
    }
}
