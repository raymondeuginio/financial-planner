@extends('layouts.app')

@section('content')
<div class="grid gap-8 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Add category</h2>
        <form action="{{ route('categories.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Color (hex)</label>
                    <input type="text" name="color" placeholder="#2563EB" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Existing categories</h2>
        <div class="mt-4 space-y-3">
            @forelse ($categories as $category)
                <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ $category->color ?? '#e2e8f0' }}33; color: {{ $category->color ?? '#334155' }}">
                            {{ $category->name }}
                        </span>
                        <p class="text-xs text-slate-500 mt-1">{{ ucfirst($category->type) }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleEdit('category-{{ $category->id }}')" class="rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button onclick="confirmDelete(event, 'Delete this category? Transactions will also be removed.')" class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                        </form>
                    </div>
                </div>
                <form id="category-{{ $category->id }}" action="{{ route('categories.update', $category) }}" method="POST" class="mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Name</label>
                            <input type="text" name="name" value="{{ $category->name }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Type</label>
                            <select name="type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                <option value="income" @selected($category->type === 'income')>Income</option>
                                <option value="expense" @selected($category->type === 'expense')>Expense</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-slate-600">Color (hex)</label>
                        <input type="text" name="color" value="{{ $category->color }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                        <button type="button" onclick="toggleEdit('category-{{ $category->id }}')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                    </div>
                </form>
            @empty
                <p class="text-sm text-slate-500">No categories yet. Create one to get started.</p>
            @endforelse
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
