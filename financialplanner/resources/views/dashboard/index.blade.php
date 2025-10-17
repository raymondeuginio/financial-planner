@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-medium text-slate-500">Pemasukan ({{ now()->isoFormat('MMM YYYY') }})</h3>
            <p class="mt-3 text-2xl font-semibold text-emerald-600">@idr($totalIncome)</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-medium text-slate-500">Pengeluaran ({{ now()->isoFormat('MMM YYYY') }})</h3>
            <p class="mt-3 text-2xl font-semibold text-rose-600">@idr($totalExpense)</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-medium text-slate-500">Selisih Bersih</h3>
            <p class="mt-3 text-2xl font-semibold {{ $net >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">@idr($net)</p>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Pemasukan vs Pengeluaran</h2>
                    <p class="text-sm text-slate-500">6 bulan terakhir</p>
                </div>
                <canvas id="incomeExpenseChart" class="mt-6 h-64 w-full"></canvas>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Pengeluaran Bulan Ini per Kategori</h2>
                    <p class="text-sm text-slate-500">6 kategori teratas</p>
                </div>
                <canvas id="expenseCategoryChart" class="mt-6 h-64 w-full"></canvas>
            </div>
        </div>
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800">Ringkasan Dompet</h2>
                <ul class="mt-4 space-y-4">
                    @forelse ($wallets as $wallet)
                        <li class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $wallet->color ?? '#6366f1' }}"></span>
                                <div>
                                    <p class="text-sm font-medium text-slate-700">{{ $wallet->name }}</p>
                                    <p class="text-xs text-slate-500">{{ ['cash' => 'Tunai', 'bank' => 'Bank', 'ewallet' => 'Dompet Digital'][$wallet->type] ?? $wallet->type }}</p>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-slate-800">@idr($wallet->current_balance)</span>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">
                            Tambahkan dompet untuk mulai memantau arus kas.
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-800">Transaksi Terbaru</h2>
                <ul class="mt-4 space-y-4">
                    @forelse ($recentTransactions as $transaction)
                        <li class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-slate-700">{{ $transaction->description ?? 'Tanpa keterangan' }}</p>
                                <p class="text-xs text-slate-500">{{ $transaction->occurred_at->isoFormat('D MMM YYYY') }} • {{ $transaction->category?->name }} • {{ $transaction->wallet?->name }}</p>
                            </div>
                            <span class="text-sm font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)</span>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">
                            Belum ada transaksi. Catat transaksi pertama Anda.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart');
    const months = @json($months);
    const incomeSeries = @json($incomeSeries);
    const expenseSeries = @json($expenseSeries);

    new Chart(incomeExpenseCtx, {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Pemasukan',
                    data: incomeSeries,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderRadius: 8,
                },
                {
                    label: 'Pengeluaran',
                    data: expenseSeries,
                    backgroundColor: 'rgba(244, 63, 94, 0.7)',
                    borderRadius: 8,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
                    }
                }
            }
        }
    });

    const expenseCategoryCtx = document.getElementById('expenseCategoryChart');
    const expenseLabels = @json($expenseCategories->map(fn ($row) => $row->category?->name ?? 'Tanpa kategori')->toArray());
    const expenseValues = @json($expenseCategories->map(fn ($row) => (float) $row->total)->toArray());
    const expenseColors = @json($expenseCategories->map(fn ($row) => $row->category?->color ?? '#6366f1')->toArray());
    const othersTotal = @json($expenseOthersTotal);

    if (othersTotal > 0) {
        expenseLabels.push('Lainnya');
        expenseValues.push(othersTotal);
        expenseColors.push('#cbd5f5');
    }

    new Chart(expenseCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: expenseLabels,
            datasets: [
                {
                    data: expenseValues,
                    backgroundColor: expenseColors,
                    borderWidth: 0,
                }
            ]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>
@endpush
