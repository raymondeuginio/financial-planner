@extends('layouts.app')
@php use Illuminate\Support\Str; @endphp

@section('content')
<div class="space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid gap-4 md:grid-cols-6">
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600">Start date</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600">End date</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Type</label>
                <select name="type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    <option value="income" @selected(($filters['type'] ?? '') === 'income')>Income</option>
                    <option value="expense" @selected(($filters['type'] ?? '') === 'expense')>Expense</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Wallet</label>
                <select name="wallet_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected(($filters['wallet_id'] ?? '') == $wallet->id)>{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600">Category</label>
                <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    <option value="">All</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-slate-600">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Description or notes" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="md:col-span-6 flex flex-wrap items-center gap-2">
                <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Apply filters</button>
                <a href="{{ route('transactions.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
                <a href="{{ route('transactions.export', request()->query()) }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Export CSV</a>
                <a href="{{ route('transactions.create') }}" class="ml-auto rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">New transaction</a>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Wallet</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->occurred_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-700">{{ $transaction->description ?? 'No description' }}</p>
                                @if ($transaction->notes)
                                    <p class="text-xs text-slate-500">{{ Str::limit($transaction->notes, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold" style="background-color: {{ optional($transaction->category)->color ?? '#e2e8f0' }}33; color: {{ optional($transaction->category)->color ?? '#334155' }}">
                                    {{ optional($transaction->category)->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 rounded-full px-2.5 py-1 text-xs font-semibold" style="background-color: {{ optional($transaction->wallet)->color ?? '#cbd5f5' }}33; color: {{ optional($transaction->wallet)->color ?? '#334155' }}">
                                    {{ optional($transaction->wallet)->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="confirmDelete(event)" class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No transactions found. Try adjusting your filters or add a new transaction.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
