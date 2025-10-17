@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Transaksi</h1>
            <p class="text-sm text-slate-500">Saring, tinjau, dan ekspor catatan pemasukan serta pengeluaran Anda.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('transactions.create') }}" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-700">Tambah Transaksi</a>
            <a href="{{ route('transactions.export', request()->query()) }}" class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400">Ekspor CSV</a>
        </div>
    </div>

    <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Jenis</label>
                <select name="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach (['all' => 'Semua Jenis', 'income' => 'Pemasukan', 'expense' => 'Pengeluaran'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Dompet</label>
                <select name="wallet_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Semua Dompet</option>
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->id }}" @selected(($filters['wallet_id'] ?? '') == $wallet->id)>{{ $wallet->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Kategori</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $type => $items)
                        <optgroup label="{{ $type === 'income' ? 'Pemasukan' : 'Pengeluaran' }}">
                            @foreach ($items as $category)
                                <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Pencarian</label>
                <input type="text" name="search" placeholder="Deskripsi atau catatan" value="{{ $filters['search'] ?? '' }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" />
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button type="submit" class="inline-flex items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Terapkan Filter</button>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400">Atur Ulang</a>
            <div class="ml-auto flex items-center gap-4 text-sm">
                <span class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">Pemasukan <strong>@idr($summary['income'])</strong></span>
                <span class="flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-rose-700">Pengeluaran <strong>@idr($summary['expense'])</strong></span>
                <span class="flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-slate-700">Selisih <strong>@idr($summary['net'])</strong></span>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-800">Kalender Transaksi</h2>
                <p class="text-sm text-slate-500">{{ $calendarMonth->isoFormat('MMMM YYYY') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('transactions.index', array_merge(request()->query(), ['calendar_month' => $previousMonth])) }}" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-900">Bulan Sebelumnya</a>
                <form method="GET" class="hidden sm:block">
                    @foreach (request()->except('calendar_month') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <input type="month" name="calendar_month" value="{{ $calendarMonth->format('Y-m') }}" class="rounded-full border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500" onchange="this.form.submit()" />
                </form>
                <a href="{{ route('transactions.index', array_merge(request()->query(), ['calendar_month' => $nextMonth])) }}" class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 transition hover:border-slate-400 hover:text-slate-900">Bulan Berikutnya</a>
            </div>
        </div>
        @php
            $weekDays = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        @endphp
        <div class="grid grid-cols-7 gap-px bg-slate-200 p-px text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
            @foreach ($weekDays as $day)
                <div class="bg-slate-50 px-2 py-2">{{ $day }}</div>
            @endforeach
        </div>
        <div class="grid grid-cols-7 gap-px bg-slate-200 p-px">
            @foreach ($calendarDays as $date)
                @php
                    $isCurrentMonth = $date->isSameMonth($calendarMonth);
                    $isToday = $date->isToday();
                    $dateKey = $date->toDateString();
                    $dayTransactions = collect($calendarTransactions[$dateKey] ?? []);
                @endphp
                <div class="flex min-h-[120px] flex-col gap-2 bg-white p-2 text-left {{ $isCurrentMonth ? '' : 'bg-slate-50 text-slate-400' }}">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold {{ $isToday ? 'text-slate-900' : 'text-slate-600' }}">{{ $date->format('j') }}</span>
                        @if ($dayTransactions->isNotEmpty())
                            @php
                                $incomeTotal = $dayTransactions->where('type', 'income')->sum('amount');
                                $expenseTotal = $dayTransactions->where('type', 'expense')->sum('amount');
                                $net = $incomeTotal - $expenseTotal;
                            @endphp
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $net >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $net >= 0 ? '+' : '-' }}{{ format_idr(abs($net)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1 space-y-1">
                        @forelse ($dayTransactions->take(3) as $transaction)
                            <div class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-2 py-1 text-[11px]">
                                <span class="truncate font-medium text-slate-600">{{ $transaction->description ?? $transaction->category?->name ?? 'Transaksi' }}</span>
                                <span class="font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</span>
                            </div>
                        @empty
                            <p class="text-[11px] text-slate-400">Tidak ada data</p>
                        @endforelse
                        @if ($dayTransactions->count() > 3)
                            <p class="text-[11px] font-medium text-slate-500">+{{ $dayTransactions->count() - 3 }} transaksi lainnya</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Dompet</th>
                    <th class="px-4 py-3 text-right">Nominal</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $transaction)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-4 py-3 text-slate-600">{{ $transaction->occurred_at->isoFormat('DD MMM YYYY') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $transaction->description ?? 'Tanpa deskripsi' }}</div>
                            @if ($transaction->notes)
                                <p class="text-xs text-slate-500">{{ $transaction->notes }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium" style="background-color: {{ $transaction->category?->color ?? '#e2e8f0' }}33; color: {{ $transaction->category?->color ?? '#334155' }};">
                                {{ $transaction->category?->name ?? 'Tanpa Kategori' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $transaction->wallet?->name ?? 'Tanpa Dompet' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">Ubah</a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">Tidak ada transaksi yang ditemukan. Sesuaikan filter atau tambahkan catatan baru.</td>
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
