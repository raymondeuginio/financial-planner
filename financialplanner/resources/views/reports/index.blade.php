@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="text-sm font-medium text-slate-600">Month</label>
                <input type="month" name="month" value="{{ $selectedMonth->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Apply</button>
            <div class="ml-auto text-sm text-slate-500">
                Total Income: <span class="font-semibold text-emerald-600">@idr($totalIncome)</span>
                <span class="mx-2">•</span>
                Total Expense: <span class="font-semibold text-rose-600">@idr($totalExpense)</span>
            </div>
        </form>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-700">Weekly summary</h2>
            <canvas id="weeklyChart" class="mt-4 h-64"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-700">Expenses by category</h2>
            <canvas id="reportCategoryChart" class="mt-4 h-64"></canvas>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Category breakdown</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Expense</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categoryBreakdown as $item)
                        <tr>
                            <td class="px-4 py-3">{{ optional($item['category'])->name ?? 'Uncategorized' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">@idr($item['amount'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">No expense data for this month.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Wallet summary</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Wallet</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Income</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Expense</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($walletBreakdown as $item)
                        <tr>
                            <td class="px-4 py-3">{{ optional($item['wallet'])->name ?? 'Unassigned' }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">@idr($item['income'])</td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">@idr($item['expense'])</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">@idr($item['income'] - $item['expense'])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">No data available for wallets.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@php
    $weeklyLabels = $weeklySummary->keys();
    $weeklyIncome = $weeklySummary->map(fn ($item) => $item['income']);
    $weeklyExpense = $weeklySummary->map(fn ($item) => $item['expense']);
    $categoryLabels = $categoryBreakdown->map(fn ($item) => optional($item['category'])->name ?? 'Uncategorized');
    $categoryValues = $categoryBreakdown->map(fn ($item) => $item['amount']);
@endphp

<script>
    const weeklyChart = document.getElementById('weeklyChart');
    if (weeklyChart) {
        new Chart(weeklyChart, {
            type: 'bar',
            data: {
                labels: @json($weeklyLabels->values()),
                datasets: [
                    {
                        label: 'Income',
                        data: @json($weeklyIncome->values()),
                        backgroundColor: '#22c55e',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense',
                        data: @json($weeklyExpense->values()),
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const categoryChart = document.getElementById('reportCategoryChart');
    if (categoryChart) {
        new Chart(categoryChart, {
            type: 'doughnut',
            data: {
                labels: @json($categoryLabels->values()),
                datasets: [{
                    data: @json($categoryValues->values()),
                    backgroundColor: ['#f97316', '#f43f5e', '#6366f1', '#14b8a6', '#f59e0b', '#8b5cf6', '#10b981', '#94a3b8'],
                    borderWidth: 0,
                }]
            },
            options: {
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
</script>
@endsection
