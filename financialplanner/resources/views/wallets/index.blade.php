@extends('layouts.app')

@section('content')
<div class="grid gap-8 lg:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Add wallet</h2>
        <form action="{{ route('wallets.store') }}" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="text-sm font-medium text-slate-600">Name</label>
                <input type="text" name="name" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-slate-600">Type</label>
                    <select name="type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="ewallet">E-wallet</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium text-slate-600">Starting balance</label>
                    <input type="number" step="0.01" min="0" name="starting_balance" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="text-sm font-medium text-slate-600">Color (hex)</label>
                <input type="text" name="color" placeholder="#2563EB" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
            </div>
            <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-700">Existing wallets</h2>
        <div class="mt-4 space-y-3">
            @forelse ($wallets as $wallet)
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-700">{{ $wallet->name }}</p>
                            <p class="text-xs text-slate-500">{{ ucfirst($wallet->type) }} • Starting: @idr($wallet->starting_balance)</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleEdit('wallet-{{ $wallet->id }}')" class="rounded-md border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</button>
                            <form action="{{ route('wallets.destroy', $wallet) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button onclick="confirmDelete(event, 'Delete this wallet? Transactions will also be removed.')" class="rounded-md border border-rose-200 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50">Delete</button>
                            </form>
                        </div>
                    </div>
                    @if ($wallet->color)
                        <span class="mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold" style="background-color: {{ $wallet->color }}33; color: {{ $wallet->color }}">Color</span>
                    @endif
                </div>
                <form id="wallet-{{ $wallet->id }}" action="{{ route('wallets.update', $wallet) }}" method="POST" class="mt-3 hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Name</label>
                            <input type="text" name="name" value="{{ $wallet->name }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Type</label>
                            <select name="type" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                                <option value="cash" @selected($wallet->type === 'cash')>Cash</option>
                                <option value="bank" @selected($wallet->type === 'bank')>Bank</option>
                                <option value="ewallet" @selected($wallet->type === 'ewallet')>E-wallet</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2 mt-4">
                        <div>
                            <label class="text-sm font-medium text-slate-600">Starting balance</label>
                            <input type="number" step="0.01" min="0" name="starting_balance" value="{{ $wallet->starting_balance }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-600">Color (hex)</label>
                            <input type="text" name="color" value="{{ $wallet->color }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3">
                        <button class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Update</button>
                        <button type="button" onclick="toggleEdit('wallet-{{ $wallet->id }}')" class="text-sm font-semibold text-slate-500 hover:text-slate-700">Cancel</button>
                    </div>
                </form>
            @empty
                <p class="text-sm text-slate-500">No wallets yet. Create one to get started.</p>
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
