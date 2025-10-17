@extends('layouts.app')

@section('content')
<div class="max-w-3xl space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-xl font-semibold text-slate-700">{{ $transaction->exists ? 'Edit transaction' : 'New transaction' }}</h1>
        <form method="POST" action="{{ $transaction->exists ? route('transactions.update', $transaction) : route('transactions.store') }}" class="mt-6 space-y-4">
            @csrf
            @if ($transaction->exists)
                @method('PUT')
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Date</label>
                    <input type="date" name="occurred_at" value="{{ old('occurred_at', optional($transaction->occurred_at)->toDateString()) }}" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Type</label>
                    <select name="type" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="income" @selected(old('type', $transaction->type) === 'income')>Income</option>
                        <option value="expense" @selected(old('type', $transaction->type) === 'expense')>Expense</option>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Wallet</label>
                    <select name="wallet_id" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">Select wallet</option>
                        @foreach ($wallets as $wallet)
                            <option value="{{ $wallet->id }}" @selected(old('wallet_id', $transaction->wallet_id) == $wallet->id)>{{ $wallet->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Category</label>
                    <select name="category_id" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="">Select category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $transaction->category_id) == $category->id)>{{ $category->name }} ({{ ucfirst($category->type) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Amount</label>
                    <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $transaction->amount) }}" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Description</label>
                    <input type="text" name="description" value="{{ old('description', $transaction->description) }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="text-sm font-medium text-slate-600">Notes</label>
                <textarea name="notes" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">{{ old('notes', $transaction->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save transaction</button>
                <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
