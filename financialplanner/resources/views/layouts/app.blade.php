<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Perencana Keuangan' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui']
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white">
        <header class="sticky top-0 z-40 border-b border-white/10 backdrop-blur">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight">Perencana Keuangan</a>
                    <p class="text-sm text-slate-300">Pantau pemasukan, pengeluaran, anggaran, dan dompet dalam satu tempat.</p>
                </div>
                <nav class="hidden gap-3 text-sm font-medium sm:flex">
                    @php
                        $links = [
                            'Dasbor' => route('dashboard'),
                            'Transaksi' => route('transactions.index'),
                            'Anggaran' => route('budgets.index'),
                            'Kategori' => route('categories.index'),
                            'Dompet' => route('wallets.index'),
                            'Laporan' => route('reports.index'),
                        ];
                    @endphp
                    @foreach ($links as $label => $url)
                        @php
                            $isActive = url()->current() === $url;
                        @endphp
                        <a href="{{ $url }}" class="rounded-full px-4 py-2 transition {{ $isActive ? 'bg-white/20 text-white' : 'text-slate-200 hover:bg-white/10' }}">{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
        </header>
    </div>

    <main class="mx-auto w-full max-w-5xl px-4 py-10">
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="py-6 text-center text-sm text-slate-500">
        &copy; {{ date('Y') }} Perencana Keuangan. Dibangun dengan Laravel &amp; Tailwind.
    </footer>

    @stack('scripts')
</body>
</html>
