<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Commission Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    @php
        $commissions = $commissions ?? collect();
        $pokemonCommissions = $pokemonCommissions ?? collect();
    @endphp

    <div class="min-h-screen">

        {{-- Header --}}
        <nav class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:px-8 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-xl text-white shadow-sm">
                        ₱
                    </div>

                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">
                            Agent Commission Report
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Track your own commission from downline players only.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('agent.dashboard') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700">
                        Back
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Filter --}}
            <section class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('agent.commissions.index') }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Date
                            </label>

                            <input
                                type="date"
                                name="date"
                                value="{{ $selectedDate ?? '' }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Game
                            </label>

                            <select
                                name="game"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="overall" {{ ($game ?? 'overall') === 'overall' ? 'selected' : '' }}>
                                    Overall
                                </option>

                                <option value="totalizator" {{ ($game ?? 'overall') === 'totalizator' ? 'selected' : '' }}>
                                    Totalizator
                                </option>

                                <option value="pokemon" {{ ($game ?? 'overall') === 'pokemon' ? 'selected' : '' }}>
                                    Pokémon Game
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Search
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ $search ?? request('search') }}"
                                placeholder="Search downline player..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100"
                            >
                        </div>

                        <div class="flex items-end">
                            <button class="w-full rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                Filter
                            </button>
                        </div>

                        <div class="flex items-end">
                            <a href="{{ route('agent.commissions.index') }}"
                               class="w-full rounded-xl bg-slate-200 px-5 py-3 text-center text-sm font-black text-slate-800 transition hover:bg-slate-300">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Main Summary --}}
            <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Downline Bet Amount
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">
                                ₱{{ number_format($totalBetAmount ?? 0, 2) }}
                            </h2>
                        </div>

                        <span class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-600">
                            BETS
                        </span>
                    </div>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                My Commission
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-emerald-600">
                                ₱{{ number_format($totalAgentCommission ?? 0, 2) }}
                            </h2>
                        </div>

                        <span class="rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">
                            3%
                        </span>
                    </div>
                </div>

            </section>

            {{-- Game Breakdown --}}
            <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="h-2 bg-gradient-to-r from-slate-600 to-slate-900"></div>

                    <div class="p-6">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Totalizator Commission
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Commission generated from your downline totalizator bets.
                                </p>
                            </div>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                TOTALIZATOR
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                    Bet
                                </p>

                                <p class="mt-2 text-lg font-black text-slate-900">
                                    ₱{{ number_format($totalizatorBetAmount ?? 0, 2) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-emerald-700">
                                    My Commission
                                </p>

                                <p class="mt-2 text-lg font-black text-emerald-700">
                                    ₱{{ number_format($totalizatorAgentCommission ?? 0, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="h-2 bg-gradient-to-r from-violet-500 to-fuchsia-600"></div>

                    <div class="p-6">
                        <div class="mb-5 flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Pokémon Commission
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Pokémon bet amount shown is only your downline player's own bet.
                                </p>
                            </div>

                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">
                                POKÉMON
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                    Downline Bet
                                </p>

                                <p class="mt-2 text-lg font-black text-slate-900">
                                    ₱{{ number_format($pokemonBetAmount ?? 0, 2) }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-emerald-700">
                                    My Commission
                                </p>

                                <p class="mt-2 text-lg font-black text-emerald-700">
                                    ₱{{ number_format($pokemonAgentCommission ?? 0, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </section>

            {{-- Wallet / Downline Summary --}}
            <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Downline Players
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-slate-900">
                        {{ number_format($totalDownlinePlayers ?? 0) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Downline Wallet Total
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-orange-600">
                        ₱{{ number_format($totalDownlineWallet ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-purple-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        My Wallet Balance
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-purple-600">
                        ₱{{ number_format($agentWallet ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        My Commission Wallet
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-emerald-600">
                        ₱{{ number_format($agentCommissionWallet ?? 0, 2) }}
                    </h2>

                    <form method="POST"
                          action="{{ route('agent.commissions.convertToWallet') }}"
                          class="mt-4"
                          onsubmit="return confirm('Convert this commission amount to your wallet?')">
                        @csrf

                        <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                            Amount to Convert
                        </label>

                        <input
                            type="number"
                            name="amount"
                            step="0.01"
                            min="0.01"
                            max="{{ $agentCommissionWallet ?? 0 }}"
                            value="{{ old('amount') }}"
                            placeholder="Enter amount"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                            {{ ($agentCommissionWallet ?? 0) <= 0 ? 'disabled' : '' }}
                            required
                        >

                        <button
                            type="submit"
                            class="mt-3 w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                            {{ ($agentCommissionWallet ?? 0) <= 0 ? 'disabled' : '' }}>
                            Convert to Wallet
                        </button>
                    </form>
                </div>

            </section>

            {{-- Totalizator Records --}}
            @if (($game ?? 'overall') === 'overall' || ($game ?? 'overall') === 'totalizator')
                <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Totalizator Downline Commission Records
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Totalizator commissions from your downline players.
                                </p>
                            </div>

                            <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600">
                                {{ ($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $commissions->total() : count($commissions ?? []) }} RECORDS
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-4">Date</th>
                                    <th class="px-5 py-4">Player</th>
                                    <th class="px-5 py-4 text-right">Bet Amount</th>
                                    <th class="px-5 py-4 text-right">My Commission</th>
                                    <th class="px-5 py-4">Side</th>
                                    <th class="px-5 py-4">Odds</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($commissions as $commission)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $commission->created_at?->format('M d, Y h:i A') }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-900">
                                            {{ $commission->player?->username ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-slate-900">
                                            ₱{{ number_format($commission->bet_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-emerald-600">
                                            ₱{{ number_format($commission->commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">
                                                {{ strtoupper($commission->side ?? 'N/A') }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $commission->odds ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                            No totalizator commission records found for your downline.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($commissions, 'links'))
                        <div class="border-t border-slate-200 px-6 py-4">
                            {{ $commissions->links() }}
                        </div>
                    @endif
                </section>
            @endif

            {{-- Pokémon Records --}}
            @if (($game ?? 'overall') === 'overall' || ($game ?? 'overall') === 'pokemon')
                <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Pokémon Downline Commission Records
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Bet amount shown is only the downline player's own bet, not the full battle pot.
                                </p>
                            </div>

                            <span class="w-fit rounded-full bg-violet-50 px-4 py-2 text-xs font-black text-violet-700">
                                {{ ($pokemonCommissions instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $pokemonCommissions->total() : count($pokemonCommissions ?? []) }} RECORDS
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-4">Date</th>
                                    <th class="px-5 py-4">Player</th>
                                    <th class="px-5 py-4">Event</th>
                                    <th class="px-5 py-4">Round</th>
                                    <th class="px-5 py-4 text-right">Downline Bet</th>
                                    <th class="px-5 py-4 text-right">My Commission</th>
                                    <th class="px-5 py-4">Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @forelse ($pokemonCommissions as $report)
                                    <tr class="transition hover:bg-slate-50">
                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $report->settled_at?->format('M d, Y h:i A') }}
                                        </td>

                                        <td class="px-5 py-4 font-bold text-slate-900">
                                            {{ $report->player_name ?? $report->player?->username ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4">
                                            {{ $report->event_name ?? 'Pokemon Battle Room' }}
                                        </td>

                                        <td class="px-5 py-4">
                                            {{ $report->round_label ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-slate-900">
                                            ₱{{ number_format($report->total_bet_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-emerald-600">
                                            ₱{{ number_format($report->agent_commission_amount ?? $report->commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">
                                                {{ $report->status ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                                            No Pokémon commission records found for your downline.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($pokemonCommissions, 'links'))
                        <div class="border-t border-slate-200 px-6 py-4">
                            {{ $pokemonCommissions->links() }}
                        </div>
                    @endif
                </section>
            @endif

        </main>
    </div>

</body>
</html>