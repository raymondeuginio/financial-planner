@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Anggaran Bulanan</h1>
            <p class="text-sm text-slate-500">Atur batas pengeluaran dan pantau realisasi transaksi per bulan.</p>
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
                <input type="month" name="month" value="{{ old('month', $selectedMonth->format('Y-m')) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal Anggaran</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $budget->amount) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Simpan Anggaran</button>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-800">Pilih Bulan</h2>
        <p class="text-sm text-slate-500">Klik salah satu bulan untuk meninjau anggaran dan transaksi.</p>
        <div class="mt-4 grid gap-2 sm:grid-cols-4">
            @foreach ($months as $month)
                @php($isActive = $month->isSameMonth($selectedMonth))
                <a href="{{ route('budgets.index', ['month' => $month->format('Y-m')]) }}" class="flex items-center justify-center rounded-xl border px-3 py-2 text-sm font-medium transition {{ $isActive ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}">
                    {{ $month->isoFormat('MMM YYYY') }}
                </a>
            @endforeach
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-[1.4fr,1fr]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Bulan</th>
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
                                <td class="px-4 py-3 text-slate-600">{{ $item->month->isoFormat('MMM YYYY') }}</td>
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
                                                    <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Nominal Anggaran</label>
                                                    <input type="number" name="amount" step="0.01" min="0" value="{{ $item->amount }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                                </div>
                                                <button type="submit" class="w-full rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">Perbarui</button>
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
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada anggaran untuk bulan ini. Tambahkan anggaran agar pengeluaran lebih terkontrol.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800">Ringkasan Bulan {{ $selectedMonth->isoFormat('MMMM YYYY') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-600">Total Pemasukan</p>
                        <p class="mt-2 text-xl font-semibold text-emerald-700">@idr($summary['income'])</p>
                    </div>
                    <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-rose-600">Total Pengeluaran</p>
                        <p class="mt-2 text-xl font-semibold text-rose-700">@idr($summary['expense'])</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Selisih</p>
                        <p class="mt-2 text-xl font-semibold {{ $summary['net'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">@idr($summary['net'])</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800">Transaksi Bulan Ini</h2>
                <span class="text-xs font-medium text-slate-500">{{ $transactions->count() }} transaksi</span>
            </div>
            <ul class="mt-4 space-y-4">
                @forelse ($transactions as $transaction)
                    <li class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $transaction->description ?? 'Tanpa keterangan' }}</p>
                                <p class="text-xs text-slate-500">{{ $transaction->occurred_at->isoFormat('D MMMM YYYY') }} • {{ $transaction->category?->name ?? 'Tidak ada kategori' }} • {{ $transaction->wallet?->name ?? 'Tidak ada dompet' }}</p>
                                @if ($transaction->notes)
                                    <p class="mt-2 text-xs text-slate-500">Catatan: {{ $transaction->notes }}</p>
                                @endif
                            </div>
                            <span class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</span>
                        </div>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-slate-200 p-6 text-center text-sm text-slate-500">Belum ada transaksi pada bulan ini.</li>
                @endforelse
            </ul>
        </div>
    </section>
</div>
@endsection
