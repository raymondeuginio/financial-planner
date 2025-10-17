@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Reports</h1>
            <p class="text-sm text-slate-500">Visualise trends and compare wallets and categories.</p>
        </div>
        <form method="GET" class="flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-sm">
            <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Month</label>
            <input type="month" name="month" value="{{ $selectedMonth->format('Y-m') }}" class="rounded-full border-0 text-sm focus:ring-0" />
            <button type="submit" class="rounded-full bg-slate-900 px-4 py-1.5 text-sm font-medium text-white transition hover:bg-slate-700">Apply</button>
        </form>
    </div>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Monthly Income vs Expense</h2>
                    <p class="text-sm text-slate-500">Last 6 months</p>
                </div>
                <canvas id="monthlyChart" class="mt-6 h-64 w-full"></canvas>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-800">Weekly Breakdown ({{ $selectedMonth->isoFormat('MMM YYYY') }})</h2>
                    <p class="text-sm text-slate-500">Income vs expense per ISO week</p>
                </div>
                <canvas id="weeklyChart" class="mt-6 h-64 w-full"></canvas>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-800">Wallet Share ({{ $selectedMonth->isoFormat('MMM YYYY') }})</h2>
            <canvas id="walletChart" class="mt-6 h-64 w-full"></canvas>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Category Breakdown</h3>
                <p class="text-sm text-slate-500">Expenses for {{ $selectedMonth->isoFormat('MMM YYYY') }}</p>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3 text-right">Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categoryBreakdown as $category)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $category->color ?? '#6366f1' }}"></span>
                                    <span class="font-medium text-slate-800">{{ $category->name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-rose-600">@idr($category->expense_total)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-sm text-slate-500">No expenses recorded for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-4 py-4">
                <h3 class="text-lg font-semibold text-slate-800">Wallet Summary</h3>
                <p class="text-sm text-slate-500">Income and expense totals</p>
            </div>
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Wallet</th>
                        <th class="px-4 py-3 text-right">Income</th>
                        <th class="px-4 py-3 text-right">Expense</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($walletBreakdown as $wallet)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $wallet->color ?? '#22c55e' }}"></span>
                                    <span class="font-medium text-slate-800">{{ $wallet->name }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-emerald-600">@idr($wallet->income_total)</td>
                            <td class="px-4 py-3 text-right font-medium text-rose-600">@idr($wallet->expense_total)</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">No data yet. Add transactions to see wallet performance.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    const monthlyCtx = document.getElementById('monthlyChart');
    const monthLabels = @json($months->map(fn ($month) => $month->isoFormat('MMM YYYY')));
    const monthlyIncome = @json($incomeSeries);
    const monthlyExpense = @json($expenseSeries);

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthLabels,
            datasets: [
                { label: 'Income', data: monthlyIncome, backgroundColor: 'rgba(16, 185, 129, 0.7)', borderRadius: 8 },
                { label: 'Expense', data: monthlyExpense, backgroundColor: 'rgba(244, 63, 94, 0.7)', borderRadius: 8 }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: {
                    ticks: {
                        callback: value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
                    }
                }
            }
        }
    });

    const weeklyCtx = document.getElementById('weeklyChart');
    const weeklyData = @json($weeklyData);

    new Chart(weeklyCtx, {
        type: 'line',
        data: {
            labels: weeklyData.map(item => item.label),
            datasets: [
                { label: 'Income', data: weeklyData.map(item => item.income), borderColor: 'rgba(16, 185, 129, 1)', backgroundColor: 'rgba(16, 185, 129, 0.1)', tension: 0.4, fill: true },
                { label: 'Expense', data: weeklyData.map(item => item.expense), borderColor: 'rgba(244, 63, 94, 1)', backgroundColor: 'rgba(244, 63, 94, 0.1)', tension: 0.4, fill: true }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: {
                y: {
                    ticks: {
                        callback: value => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
                    }
                }
            }
        }
    });

    const walletCtx = document.getElementById('walletChart');
    const walletLabels = @json($walletBreakdown->map(fn ($wallet) => $wallet->name));
    const walletExpenses = @json($walletBreakdown->map(fn ($wallet) => (float) $wallet->expense_total));
    const walletColors = @json($walletBreakdown->map(fn ($wallet) => $wallet->color ?? '#22c55e'));

    new Chart(walletCtx, {
        type: 'doughnut',
        data: {
            labels: walletLabels,
            datasets: [{ data: walletExpenses, backgroundColor: walletColors, borderWidth: 0 }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>
@endpush
