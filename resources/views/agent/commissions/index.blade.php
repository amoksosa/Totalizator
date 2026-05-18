<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Commission Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="w-full max-w-[1500px] mx-auto px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Commission Dashboard
                </h1>

                <p class="text-base text-slate-500 mt-1">
                    View your 5% commission from every downline player bet.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.dashboard') }}"
                   class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                    Dashboard
                </a>

                <a href="{{ route('agent.users.index') }}"
                   class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-sm font-bold transition">
                    User Management
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-[1500px] mx-auto px-6 py-8">

        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6">
                <p class="text-sm font-bold text-emerald-700">
                    Total Commission
                </p>

                <h2 class="text-4xl font-extrabold text-emerald-800 mt-2">
                    ₱{{ number_format($totalCommission, 2) }}
                </h2>

                <p class="text-sm text-emerald-700 mt-2">
                    Lifetime commission earned.
                </p>
            </div>

            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-6">
                <p class="text-sm font-bold text-blue-700">
                    Today Commission
                </p>

                <h2 class="text-4xl font-extrabold text-blue-800 mt-2">
                    ₱{{ number_format($todayCommission, 2) }}
                </h2>

                <p class="text-sm text-blue-700 mt-2">
                    Commission earned today.
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6">
                <p class="text-sm font-bold text-slate-700">
                    Total Player Bets
                </p>

                <h2 class="text-4xl font-extrabold text-slate-900 mt-2">
                    ₱{{ number_format($totalBetAmount, 2) }}
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Total bet volume from your players.
                </p>
            </div>

            <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-6">
                <p class="text-sm font-bold text-yellow-700">
                    Today Player Bets
                </p>

                <h2 class="text-4xl font-extrabold text-yellow-800 mt-2">
                    ₱{{ number_format($todayBetAmount, 2) }}
                </h2>

                <p class="text-sm text-yellow-700 mt-2">
                    Bet volume from your players today.
                </p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <form method="GET" action="{{ route('agent.commissions.index') }}"
                  class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search player username or mobile number..."
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >

                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3 transition">
                    Search
                </button>
            </form>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">
                    Player Bet Commission History
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Every player bet under your account automatically creates a 5% commission record.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Player</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Side</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Odds</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Bet Amount</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Rate</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Commission</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($commissions as $commission)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $commission->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $commission->player?->username ?? 'Unknown Player' }}
                                    </div>

                                    <div class="text-xs text-slate-500">
                                        Mobile: {{ $commission->player?->mobile_number ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($commission->side === 'MERON')
                                        <span class="inline-flex rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif ($commission->side === 'WALA')
                                        <span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-bold text-slate-700">
                                    {{ $commission->odds }}
                                </td>

                                <td class="px-5 py-4 font-bold text-slate-900">
                                    ₱{{ number_format($commission->bet_amount, 2) }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ number_format($commission->commission_rate, 2) }}%
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-lg font-extrabold text-emerald-700">
                                        ₱{{ number_format($commission->commission_amount, 2) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <h3 class="text-xl font-bold text-slate-900">
                                        No commission history yet
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Once your players place bets, your 5% commission records will appear here.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $commissions->links() }}
        </div>
    </main>

</body>
</html>