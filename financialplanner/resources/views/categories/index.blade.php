@extends('layouts.app')

@section('content')
<div class="grid gap-8 lg:grid-cols-[1fr,1.5fr]">
    <div class="space-y-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-800">Categories</h1>
            <p class="text-sm text-slate-500">Organise income and expenses with colour-coded categories.</p>
        </div>
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" placeholder="e.g. Groceries" required />
                @error('name')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Type</label>
                <select name="type" class="w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                    @foreach (['income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('type', $category->type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-2">
                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Colour</label>
                <input type="color" name="color" value="{{ old('color', '#6366f1') }}" class="h-10 w-16 rounded" />
            </div>
            <button type="submit" class="w-full rounded-full bg-slate-900 px-5 py-2 text-sm font-medium text-white transition hover:bg-slate-700">Add Category</button>
        </form>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-800">All Categories</h2>
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Colour</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($categories as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $item->name }}</td>
                            <td class="px-4 py-3 text-slate-600 capitalize">{{ $item->type }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600">
                                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $item->color ?? '#6366f1' }}"></span>
                                    {{ $item->color ?? 'Default' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-2">
                                    <details class="group inline-block">
                                        <summary class="cursor-pointer text-xs font-medium text-slate-600 hover:text-slate-900">Edit</summary>
                                        <form action="{{ route('categories.update', $item) }}" method="POST" class="mt-2 space-y-3 rounded-xl border border-slate-200 bg-slate-50 p-4 text-left">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
                                                <input type="text" name="name" value="{{ $item->name }}" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500" required />
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Type</label>
                                                <select name="type" class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-slate-500 focus:ring-slate-500">
                                                    @foreach (['income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                                                        <option value="{{ $value }}" @selected($item->type === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Colour</label>
                                                <input type="color" name="color" value="{{ $item->color ?? '#6366f1' }}" class="h-10 w-16 rounded" />
                                            </div>
                                            <button type="submit" class="w-full rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-700">Update</button>
                                        </form>
                                    </details>
                                    <form action="{{ route('categories.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No categories yet. Start by creating your first category.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
