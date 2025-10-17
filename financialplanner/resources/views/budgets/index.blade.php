@extends('layouts.app')

@section('content')
<div class="grid gap-8 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
        <h2 class="text-lg font-semibold text-slate-700">Set budget</h2>
        <form action="{{ route('budgets.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-600">Category</label>
                <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Month</label>
                <input type="month" name="month" value="{{ now()->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Amount</label>
                <input type="number" step="0.01" min="0" name="amount" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
        <h2 class="text-lg font-semibold text-slate-700">Budgets</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Month</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Budget</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Spent</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Remaining</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($budgetSummaries as $item)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ optional($item['budget']->category)->color ?? '#e2e8f0' }}33; color: {{ optional($item['budget']->category)->color ?? '#334155' }}">
                                    {{ optional($item['budget']->category)->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ optional($item['budget']->month)->format('F Y') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-700">@idr($item['budget']->amount)</td>
                            <td class="px-4 py-3 text-right font-semibold text-rose-600">@idr($item['spent'])</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">@idr($item['remaining'])</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="toggleEdit('budget-{{ $item['budget']->id }}')" class="rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                                    <form action="{{ route('budgets.destroy', $item['budget']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="confirmDelete(event, 'Delete this budget?')" class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="budget-{{ $item['budget']->id }}" class="hidden bg-slate-50/60">
                            <td colspan="6" class="px-4 py-4">
                                <form action="{{ route('budgets.update', $item['budget']) }}" method="POST" class="grid items-end gap-4 md:grid-cols-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Category</label>
                                        <select name="category_id" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected($category->id === $item['budget']->category_id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Month</label>
                                        <input type="month" name="month" value="{{ optional($item['budget']->month)->format('Y-m') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-slate-600">Amount</label>
                                        <input type="number" step="0.01" min="0" name="amount" value="{{ $item['budget']->amount }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                                        <button type="button" onclick="toggleEdit('budget-{{ $item['budget']->id }}')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No budgets yet. Create one to start tracking your spending limits.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleEdit(id) {
        const el = document.getElementById(id);
        if (el) {
            el.classList.toggle('hidden');
        }
    }
</script>
@endsection
