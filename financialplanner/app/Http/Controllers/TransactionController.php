<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['start_date', 'end_date', 'type', 'wallet_id', 'category_id', 'search']);

        $calendarMonthInput = $request->input('calendar_month');
        try {
            $calendarMonth = Carbon::createFromFormat('Y-m', $calendarMonthInput)->startOfMonth();
        } catch (\Throwable $e) {
            $calendarMonth = Carbon::now()->startOfMonth();
        }

        $calendarStart = $calendarMonth->copy()->startOfMonth();
        $calendarEnd = $calendarMonth->copy()->endOfMonth();

        $query = Transaction::with(['wallet', 'category'])->filter($filters);

        $transactionsQuery = clone $query;

        $transactions = $transactionsQuery
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $incomeQuery = clone $query;
        $expenseQuery = clone $query;

        $summary = [
            'income' => (float) $incomeQuery->where('type', 'income')->sum('amount'),
            'expense' => (float) $expenseQuery->where('type', 'expense')->sum('amount'),
        ];
        $summary['net'] = $summary['income'] - $summary['expense'];

        $calendarFilters = $filters;
        $calendarFilters['start_date'] = $calendarStart->toDateString();
        $calendarFilters['end_date'] = $calendarEnd->toDateString();

        $calendarTransactions = Transaction::with(['wallet', 'category'])
            ->filter($calendarFilters)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Transaction $transaction) => $transaction->occurred_at->toDateString());

        $calendarRangeStart = $calendarStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarRangeEnd = $calendarEnd->copy()->endOfWeek(Carbon::SUNDAY);

        $calendarDays = [];
        for ($date = $calendarRangeStart->copy(); $date <= $calendarRangeEnd; $date->addDay()) {
            $calendarDays[] = $date->copy();
        }

        return view('transactions.index', [
            'transactions' => $transactions,
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('type')->orderBy('name')->get()->groupBy('type'),
            'filters' => $filters,
            'summary' => $summary,
            'calendarMonth' => $calendarMonth,
            'calendarDays' => $calendarDays,
            'calendarTransactions' => $calendarTransactions,
            'previousMonth' => $calendarMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $calendarMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function create(): View
    {
        return view('transactions.form', [
            'transaction' => new Transaction(['occurred_at' => now()->toDateString(), 'type' => 'expense']),
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('type')->orderBy('name')->get()->groupBy('type'),
            'method' => 'POST',
            'action' => route('transactions.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTransaction($request);

        Transaction::create($data);

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.form', [
            'transaction' => $transaction,
            'wallets' => Wallet::orderBy('name')->get(),
            'categories' => Category::orderBy('type')->orderBy('name')->get()->groupBy('type'),
            'method' => 'PUT',
            'action' => route('transactions.update', $transaction),
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $this->validateTransaction($request);

        $transaction->update($data);

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil dihapus.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['start_date', 'end_date', 'type', 'wallet_id', 'category_id', 'search']);
        $transactions = Transaction::with(['wallet', 'category'])
            ->filter($filters)
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ];

        $columns = ['Tanggal', 'Deskripsi', 'Kategori', 'Dompet', 'Jenis', 'Nominal', 'Catatan'];

        $callback = function () use ($transactions, $columns) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $columns);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->occurred_at->toDateString(),
                    $transaction->description,
                    $transaction->category?->name,
                    $transaction->wallet?->name,
                    $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran',
                    $transaction->amount,
                    $transaction->notes,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, 'transactions.csv', $headers);
    }

    protected function validateTransaction(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => ['required', Rule::exists('wallets', 'id')],
            'category_id' => ['required', Rule::exists('categories', 'id')],
            'occurred_at' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'notes' => ['nullable', 'string'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $category = Category::find($request->input('category_id'));
            $type = $request->input('type');

            if ($category && $type && $category->type !== $type) {
                $validator->errors()->add('type', 'Jenis transaksi harus sesuai dengan kategori yang dipilih.');
            }
        });

        return $validator->validate();
    }
}
