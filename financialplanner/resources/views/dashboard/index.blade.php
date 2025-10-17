@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Income</p>
            <p class="mt-2 text-2xl font-semibold text-emerald-600">@idr($totalIncome)</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Total Expense</p>
            <p class="mt-2 text-2xl font-semibold text-rose-600">@idr($totalExpense)</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Net</p>
            <p class="mt-2 text-2xl font-semibold text-slate-800">@idr($net)</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-700">Income vs Expense</h2>
            </div>
            <canvas id="incomeExpenseChart" class="mt-4 h-64"></canvas>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-700">Expenses by Category</h2>
            </div>
            <canvas id="expenseCategoryChart" class="mt-4 h-64"></canvas>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Wallet Balances</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
            @forelse ($walletBalances as $item)
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-slate-600">{{ $item['wallet']->name }}</span>
                        @if ($item['wallet']->color)
                            <span class="h-3 w-3 rounded-full" style="background-color: {{ $item['wallet']->color }}"></span>
                        @endif
                    </div>
                    <p class="mt-2 text-lg font-semibold text-slate-800">@idr($item['balance'])</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">Add wallets to start tracking balances.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-700">Recent Transactions</h2>
            <a href="{{ route('transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all</a>
        </div>
        <div class="mt-4 divide-y divide-slate-100">
            @forelse ($recentTransactions as $transaction)
                <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <div>
                        <p class="font-medium text-slate-700">{{ $transaction->description ?? 'No description' }}</p>
                        <p class="text-sm text-slate-500">{{ $transaction->occurred_at->format('d M Y') }} • {{ optional($transaction->category)->name }} • {{ optional($transaction->wallet)->name }}</p>
                    </div>
                    <p class="text-base font-semibold {{ $transaction->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $transaction->type === 'income' ? '+' : '-' }}@idr($transaction->amount)
                    </p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No transactions yet. Start by recording your first income or expense.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    const monthlySeries = @json($monthlySeries);
    const categorySeries = @json($expenseByCategory);

    const ctx1 = document.getElementById('incomeExpenseChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: monthlySeries.map(item => item.label),
                datasets: [
                    {
                        label: 'Income',
                        data: monthlySeries.map(item => item.income),
                        backgroundColor: '#22c55e',
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense',
                        data: monthlySeries.map(item => item.expense),
                        backgroundColor: '#ef4444',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                    }
                }
            }
        });
    }

    const ctx2 = document.getElementById('expenseCategoryChart');
    if (ctx2) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: categorySeries.map(item => item.name),
                datasets: [{
                    data: categorySeries.map(item => item.amount),
                    backgroundColor: categorySeries.map(item => item.color || '#cbd5f5'),
                    borderWidth: 0,
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>
@endsection
