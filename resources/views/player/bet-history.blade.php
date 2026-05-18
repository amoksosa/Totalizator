<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet History</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Bet History
                </h1>

                <p class="text-sm text-slate-500">
                    Your recent bet records, results, winnings, and payouts.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('player.dashboard') }}"
                   class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 text-sm font-semibold transition">
                    Back to Game
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-900">
                    My Bet Records
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Pending bets are waiting for the declare user to select the winner.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Side</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Odds</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Bet Amount</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Status</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Win Amount</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Payout</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Before</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">After Bet</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($bets as $bet)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $bet->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($bet->side === 'MERON')
                                        <span class="inline-flex rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif ($bet->side === 'WALA')
                                        <span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-bold text-slate-700 whitespace-nowrap">
                                    {{ $bet->odds }}
                                </td>

                                <td class="px-5 py-4 font-bold text-red-600 whitespace-nowrap">
                                    ₱{{ number_format($bet->amount, 2) }}
                                </td>

                                <td class="px-5 py-4">
                                    @if (($bet->status ?? 'pending') === 'won')
                                        <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            Won
                                        </span>
                                    @elseif (($bet->status ?? 'pending') === 'lost')
                                        <span class="inline-flex rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            Lost
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-bold text-green-700 whitespace-nowrap">
                                    ₱{{ number_format($bet->win_amount ?? 0, 2) }}
                                </td>

                                <td class="px-5 py-4 font-bold text-blue-700 whitespace-nowrap">
                                    ₱{{ number_format($bet->payout_amount ?? 0, 2) }}
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    ₱{{ number_format($bet->balance_before, 2) }}
                                </td>

                                <td class="px-5 py-4 font-bold text-emerald-700 whitespace-nowrap">
                                    ₱{{ number_format($bet->balance_after, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center">
                                    <h3 class="text-xl font-bold text-slate-900">
                                        No bet history yet
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Your bets will appear here after you place a bet.
                                    </p>

                                    <a href="{{ route('player.dashboard') }}"
                                       class="inline-block mt-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold transition">
                                        Go to Game
                                    </a>
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