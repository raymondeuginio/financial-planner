<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script type="module">
        import { create } from 'https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4';

        create({
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
                    },
                },
            },
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <div class="bg-gradient-to-r from-sky-500/10 via-indigo-500/10 to-purple-500/10 border-b border-slate-200 sticky top-0 z-40 backdrop-blur">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-slate-800">{{ config('app.name', 'Finance Planner') }}</a>
            <nav class="flex gap-4 text-sm text-slate-600">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-900 {{ request()->routeIs('dashboard') ? 'font-semibold text-slate-900' : '' }}">Dashboard</a>
                <a href="{{ route('transactions.index') }}" class="hover:text-slate-900 {{ request()->routeIs('transactions.*') ? 'font-semibold text-slate-900' : '' }}">Transactions</a>
                <a href="{{ route('budgets.index') }}" class="hover:text-slate-900 {{ request()->routeIs('budgets.*') ? 'font-semibold text-slate-900' : '' }}">Budgets</a>
                <a href="{{ route('categories.index') }}" class="hover:text-slate-900 {{ request()->routeIs('categories.*') ? 'font-semibold text-slate-900' : '' }}">Categories</a>
                <a href="{{ route('wallets.index') }}" class="hover:text-slate-900 {{ request()->routeIs('wallets.*') ? 'font-semibold text-slate-900' : '' }}">Wallets</a>
                <a href="{{ route('reports.index') }}" class="hover:text-slate-900 {{ request()->routeIs('reports.*') ? 'font-semibold text-slate-900' : '' }}">Reports</a>
            </nav>
        </div>
    </div>
    <main class="max-w-5xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-700">
                <p class="font-medium">There were some problems with your submission.</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{ $slot ?? '' }}
        @yield('content')
    </main>
    <script>
        function confirmDelete(event, message = 'Are you sure you want to delete this item?') {
            if (!confirm(message)) {
                event.preventDefault();
            }
        }
    </script>
</body>
</html>
