<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet History</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#1d4ed8_0,#020617_45%,#020617_100%)]">

        {{-- Navbar --}}
        <nav class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/85 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">
                            Bet History
                        </h1>

                        <p class="mt-1 text-sm text-slate-400">
                            View your Totalizator bets, results, winnings, and payouts.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('player.dashboard') }}"
                           class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-sm font-bold text-white transition hover:bg-white/20">
                            Dashboard
                        </a>

                        <a href="{{ route('player.totalizator') }}"
                           class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                            Back to Game
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700">
                                Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8">

            {{-- Header Card --}}
            <section class="mb-6 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur">
                <div class="relative p-6 md:p-8">
                    <div class="absolute right-0 top-0 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"></div>

                    <div class="relative grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div class="md:col-span-2">
                            <p class="text-sm font-black uppercase tracking-wide text-blue-300">
                                Player Records
                            </p>

                            <h2 class="mt-3 text-3xl font-black text-white md:text-4xl">
                                My Bet Records
                            </h2>

                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-300">
                                Pending bets are waiting for the declare user to close the round and declare the final winner.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            <p class="text-sm font-bold text-emerald-300">
                                Current Balance
                            </p>

                            <p class="mt-2 text-3xl font-black text-white">
                                ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                Your available wallet balance.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Bet History Table --}}
            <section class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur">
                <div class="border-b border-white/10 px-5 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-xl font-black text-white">
                                Totalizator Bet History
                            </h2>

                            <p class="mt-1 text-sm text-slate-400">
                                Scroll sideways on mobile to view all columns.
                            </p>
                        </div>

                        <span class="w-fit rounded-full border border-blue-300/20 bg-blue-500/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-blue-200">
                            Latest Records
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1150px] text-sm">
                        <thead class="bg-slate-900/90 text-slate-200">
                            <tr>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Side</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Odds</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Bet Amount</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Status</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Win Amount</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Payout</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">Before</th>
                                <th class="px-5 py-4 text-right whitespace-nowrap">After Bet</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @forelse ($bets as $bet)
                                <tr class="transition hover:bg-white/10">
                                    <td class="px-5 py-4 whitespace-nowrap text-slate-300">
                                        {{ $bet->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($bet->side === 'MERON')
                                            <span class="inline-flex rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-200 ring-1 ring-red-400/20">
                                                MERON
                                            </span>
                                        @elseif ($bet->side === 'WALA')
                                            <span class="inline-flex rounded-full bg-blue-500/15 px-3 py-1 text-xs font-black text-blue-200 ring-1 ring-blue-400/20">
                                                WALA
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-emerald-500/15 px-3 py-1 text-xs font-black text-emerald-200 ring-1 ring-emerald-400/20">
                                                DRAW
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 font-black text-white whitespace-nowrap">
                                        {{ $bet->odds }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-red-200 whitespace-nowrap">
                                        ₱{{ number_format($bet->amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if (($bet->status ?? 'pending') === 'won')
                                            <span class="inline-flex rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-200 ring-1 ring-green-400/20">
                                                Won
                                            </span>
                                        @elseif (($bet->status ?? 'pending') === 'lost')
                                            <span class="inline-flex rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-200 ring-1 ring-red-400/20">
                                                Lost
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-yellow-500/15 px-3 py-1 text-xs font-black text-yellow-200 ring-1 ring-yellow-400/20">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-green-200 whitespace-nowrap">
                                        ₱{{ number_format($bet->win_amount ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-blue-200 whitespace-nowrap">
                                        ₱{{ number_format($bet->payout_amount ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right text-slate-300 whitespace-nowrap">
                                        ₱{{ number_format($bet->balance_before, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-emerald-200 whitespace-nowrap">
                                        ₱{{ number_format($bet->balance_after, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-16 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-500/15 text-3xl ring-1 ring-blue-400/20">
                                                📜
                                            </div>

                                            <h3 class="mt-5 text-2xl font-black text-white">
                                                No bet history yet
                                            </h3>

                                            <p class="mt-2 text-sm text-slate-400">
                                                Your bets will appear here after you place your first bet.
                                            </p>

                                            <a href="{{ route('player.totalizator') }}"
                                               class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                                                Go to Totalizator
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="mt-6">
                {{ $bets->links() }}
            </div>

        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userId = @json(auth()->id());

            if (!window.Echo) {
                console.error('Laravel Echo is not loaded.');
                return;
            }

            window.Echo.channel('user.' + userId)
                .listen('.force.logout', function (event) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Logged Out',
                        text: event.message,
                        confirmButtonColor: '#dc2626',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.href = event.redirect_url;
                    });

                    setTimeout(function () {
                        window.location.href = event.redirect_url;
                    }, 2500);
                });
        });
    </script>

</body>
</html>