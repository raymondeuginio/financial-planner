@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">{{ $transaction->exists ? 'Edit Transaction' : 'Add Transaction' }}</h1>
        <p class="text-sm text-slate-500">Record income or expense with the right wallet and category.</p>
    </div>

    <form action="{{ $action }}" method="POST" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Type</label>
                <select name="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach (['income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $transaction->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
                <input type="date" name="occurred_at" value="{{ old('occurred_at', optional($transaction->occurred_at)->toDateString()) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                @error('occurred_at')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Wallet</label>
                <select name="wallet_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected(old('wallet_id', $transaction->wallet_id) == $wallet->id)>{{ $wallet->name }} ({{ ucfirst($wallet->type) }})</option>
                    @endforeach
                </select>
                @error('wallet_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($categories as $type => $items)
                        <optgroup label="{{ ucfirst($type) }}">
                            @foreach ($items as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $transaction->category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Amount (Rp)</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                @error('amount')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Description</label>
                <input type="text" name="description" value="{{ old('description', $transaction->description) }}" placeholder="e.g. Dinner with friends" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Optional details">{{ old('notes', $transaction->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</a>
            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Save Transaction</button>
        </div>
    </form>
</div>
@endsection
