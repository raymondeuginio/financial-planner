@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-800">Anggaran Bulanan</h1>
                <p class="text-sm text-slate-500">Tetapkan batas belanja per kategori dan pantau realisasinya per bulan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('budgets.index', ['month' => $selectedMonth->copy()->subYear()->format('Y-m')]) }}" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800">Tahun Sebelumnya</a>
                <span class="rounded-full bg-slate-900 px-4 py-1 text-xs font-semibold text-white">{{ $selectedMonth->format('Y') }}</span>
                <a href="{{ route('budgets.index', ['month' => $selectedMonth->copy()->addYear()->format('Y-m')]) }}" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-800">Tahun Berikutnya</a>
            </div>
        </div>

        <div class="grid gap-2 sm:grid-cols-6 lg:grid-cols-12">
            @foreach ($monthGrid as $monthOption)
                @php
                    $isActive = $monthOption->isSameMonth($selectedMonth);
                @endphp
                <a href="{{ route('budgets.index', ['month' => $monthOption->format('Y-m')]) }}" class="flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-medium transition {{ $isActive ? 'border-slate-900 bg-slate-900 text-white shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-400 hover:text-slate-900' }}">
                    {{ $monthOption->isoFormat('MMM') }}
                </a>
            @endforeach
        </div>

        <form action="{{ route('budgets.store') }}" method="POST" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1.5fr,1fr,1fr,auto]">
            @csrf
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Kategori</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Bulan</label>
                <input type="month" name="month" value="{{ old('month', $budget->month?->format('Y-m')) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $budget->amount) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Simpan Anggaran</button>
            </div>
        </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.5fr,1fr]">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Rincian Anggaran</h2>
                    <p class="text-sm text-slate-500">{{ $selectedMonth->isoFormat('MMMM YYYY') }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $budgets->count() }} kategori</span>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3 text-right">Anggaran</th>
                        <th class="px-4 py-3 text-right">Realisasi</th>
                        <th class="px-4 py-3 text-right">Sisa</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($budgets as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $item->category?->color ?? '#6366f1' }}"></span>
                                    <span class="font-medium text-slate-800">{{ $item->category?->name ?? 'Tanpa Kategori' }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-slate-700">@idr($item->amount)</td>
                            <td class="px-4 py-3 text-right font-medium text-rose-600">@idr($item->spent_amount)</td>
                            <td class="px-4 py-3 text-right font-medium {{ $item->remaining_amount >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">@idr($item->remaining_amount)</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <details class="group inline-block">
                                        <summary class="cursor-pointer text-xs font-medium text-slate-600 hover:text-slate-900">Ubah</summary>
                                        <form action="{{ route('budgets.update', $item) }}" method="POST" class="mt-2 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Bulan</label>
                                                <input type="month" name="month" value="{{ $item->month->format('Y-m') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal</label>
                                                <input type="number" name="amount" step="0.01" min="0" value="{{ $item->amount }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                            </div>
                                            <button type="submit" class="w-full rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">Simpan</button>
                                        </form>
                                    </details>
                                    <form action="{{ route('budgets.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus anggaran ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada anggaran untuk bulan ini. Tambahkan kategori agar pengeluaran lebih terkontrol.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800">Transaksi Bulan Ini</h2>
                    <p class="text-sm text-slate-500">{{ $selectedMonth->isoFormat('MMMM YYYY') }}</p>
                </div>
                <a href="{{ route('transactions.index', ['start_date' => $selectedMonth->copy()->startOfMonth()->toDateString(), 'end_date' => $selectedMonth->copy()->endOfMonth()->toDateString()]) }}" class="text-xs font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline">Lihat di halaman transaksi</a>
            </div>
            <ul class="max-h-96 space-y-3 overflow-y-auto p-4">
                @forelse ($monthlyTransactions as $transaction)
                    <li class="rounded-xl border border-slate-200 p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-slate-500">{{ $transaction->occurred_at->isoFormat('DD MMM YYYY') }}</span>
                            <span class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</span>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $transaction->description ?? 'Tanpa deskripsi' }}</p>
                        <p class="text-xs text-slate-500">{{ $transaction->category?->name ?? 'Tanpa Kategori' }} • {{ $transaction->wallet?->name ?? 'Tanpa Dompet' }}</p>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">Belum ada transaksi pada bulan ini.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
