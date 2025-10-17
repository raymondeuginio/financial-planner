@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Transactions</h1>
            <p class="text-sm text-slate-500">Filter, review, and export your income &amp; expense history.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-700">Add Transaction</a>
            <a href="{{ route('transactions.export', request()->query()) }}" class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400">Export CSV</a>
        </div>
    </div>

    <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Start Date</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">End Date</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Type</label>
                <select name="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach (['all' => 'All Types', 'income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Wallet</label>
                <select name="wallet_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Wallets</option>
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected(($filters['wallet_id'] ?? '') == $wallet->id)>{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">All Categories</option>
                    @foreach ($categories as $type => $items)
                        <optgroup label="{{ ucfirst($type) }}">
                            @foreach ($items as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="text" name="search" placeholder="Description or notes" value="{{ $filters['search'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Apply Filters</button>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400">Reset</a>
            <div class="ml-auto flex items-center gap-4 text-sm">
                <span class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">Income <strong>@idr($summary['income'])</strong></span>
                <span class="flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-rose-700">Expense <strong>@idr($summary['expense'])</strong></span>
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-700">Net <strong>@idr($summary['net'])</strong></span>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Wallet</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $transaction)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3 text-slate-600">{{ $transaction->occurred_at->isoFormat('MMM D, YYYY') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $transaction->description ?? 'No description' }}</div>
                            @if ($transaction->notes)
                                <p class="text-xs text-slate-500">{{ $transaction->notes }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $transaction->category?->color ?? '#e2e8f0' }}33; color: {{ $transaction->category?->color ?? '#334155' }};">
                                {{ $transaction->category?->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $transaction->wallet?->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">Edit</a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Delete this transaction?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No transactions found. Try adjusting your filters or add a new entry.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
