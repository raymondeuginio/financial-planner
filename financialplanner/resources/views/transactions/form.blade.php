@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-800">{{ $transaction->exists ? 'Ubah Transaksi' : 'Catat Transaksi' }}</h1>
        <p class="text-sm text-slate-500">Catat pemasukan atau pengeluaran sesuai dompet dan kategori.</p>
    </div>

    <form action="{{ $action }}" method="POST" class="space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Jenis</label>
                <select name="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach (['income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $transaction->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal</label>
                <input type="date" name="occurred_at" value="{{ old('occurred_at', optional($transaction->occurred_at)->toDateString()) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                @error('occurred_at')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Dompet</label>
                <select name="wallet_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected(old('wallet_id', $transaction->wallet_id) == $wallet->id)>{{ $wallet->name }} ({{ ['cash' => 'Tunai', 'bank' => 'Bank', 'ewallet' => 'Dompet Digital'][$wallet->type] ?? $wallet->type }})</option>
                    @endforeach
                </select>
                @error('wallet_id')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Kategori</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($categories as $type => $items)
                        <optgroup label="{{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
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
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal (Rp)</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $transaction->amount) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                @error('amount')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Keterangan</label>
                <input type="text" name="description" value="{{ old('description', $transaction->description) }}" placeholder="Contoh: Makan malam bersama teman" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Catatan</label>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="Detail tambahan (opsional)">{{ old('notes', $transaction->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('transactions.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Batal</a>
            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Simpan Transaksi</button>
        </div>
    </form>
</div>
@endsection
