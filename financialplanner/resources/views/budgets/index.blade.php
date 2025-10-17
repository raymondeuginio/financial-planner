@extends('layouts.app')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Budgets</h1>
            <p class="text-sm text-slate-500">Set monthly spending limits for expense categories.</p>
        </div>
        <form action="{{ route('budgets.store') }}" method="POST" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1.5fr,1fr,1fr,auto]">
            @csrf
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</label>
                <select name="category_id" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Month</label>
                <input type="month" name="month" value="{{ old('month', $budget->month?->format('Y-m')) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div>
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Amount</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $budget->amount) }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
            </div>
            <div class="flex items-end">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Save Budget</button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Month</th>
                    <th class="px-4 py-3 text-right">Budget</th>
                    <th class="px-4 py-3 text-right">Spent</th>
                    <th class="px-4 py-3 text-right">Remaining</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($budgets as $item)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full" style="background-color: {{ $item->category?->color ?? '#6366f1' }}"></span>
                                <span class="font-medium text-slate-800">{{ $item->category?->name ?? 'N/A' }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $item->month->isoFormat('MMM YYYY') }}</td>
                        <td class="px-4 py-3 text-right font-medium text-slate-700">@idr($item->amount)</td>
                        <td class="px-4 py-3 text-right font-medium text-rose-600">@idr($item->spent_amount)</td>
                        <td class="px-4 py-3 text-right font-medium {{ $item->remaining_amount >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">@idr($item->remaining_amount)</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <details class="group inline-block">
                                    <summary class="cursor-pointer text-xs font-medium text-slate-600 hover:text-slate-900">Edit</summary>
                                    <form action="{{ route('budgets.update', $item) }}" method="POST" class="mt-2 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Month</label>
                                            <input type="month" name="month" value="{{ $item->month->format('Y-m') }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Amount</label>
                                            <input type="number" name="amount" step="0.01" min="0" value="{{ $item->amount }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                        </div>
                                        <button type="submit" class="w-full rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">Update</button>
                                    </form>
                                </details>
                                <form action="{{ route('budgets.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this budget?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No budgets yet. Create one to start planning your spending.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
