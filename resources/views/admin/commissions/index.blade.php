<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen">

        {{-- Top Navigation --}}
        <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900">
                        Daily Sales Report
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Monitor totalizator, Pokémon commissions, wallet totals, and agent withdrawals.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                        Back
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Filter Card --}}
            <section class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET" action="{{ route('admin.commissions.index') }}">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Date
                            </label>

                            <input
                                type="date"
                                name="date"
                                value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Game
                            </label>

                            <select
                                name="game"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
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

                        <div class="md:col-span-1">
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">
                                Search
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ $search ?? request('search') }}"
                                placeholder="Agent, player, event..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >
                        </div>

                        <div class="flex items-end">
                            <button class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                Filter
                            </button>
                        </div>

                        <div class="flex items-end">
                            <a href="{{ route('admin.commissions.index') }}"
                               class="w-full rounded-xl bg-slate-200 px-5 py-3 text-center text-sm font-black text-slate-800 transition hover:bg-slate-300">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Main Summary Cards --}}
            <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Bet Amount
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900">
                                ₱{{ number_format($totalBetAmount ?? 0, 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-600">
                            BETS
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Agent Commission 3%
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-emerald-600">
                                ₱{{ number_format($totalAgentCommission ?? 0, 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">
                            3%
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Company Commission 2%
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-blue-600">
                                ₱{{ number_format($totalCompanyCommission ?? 0, 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700">
                            2%
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-red-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Commission 5%
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-red-600">
                                ₱{{ number_format($totalCommission ?? 0, 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-red-50 px-3 py-2 text-xs font-black text-red-700">
                            5%
                        </div>
                    </div>
                </div>
            </section>

            {{-- Game Summary --}}
            <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">
                                Totalizator Commission
                            </h2>
                            <p class="text-sm text-slate-500">
                                Commission generated from totalizator player bets.
                            </p>
                        </div>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                            TOTALIZATOR
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Bet
                            </p>
                            <p class="mt-2 text-lg font-black text-slate-900">
                                ₱{{ number_format($totalizatorBetAmount ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">
                                Agent
                            </p>
                            <p class="mt-2 text-lg font-black text-emerald-700">
                                ₱{{ number_format($totalizatorAgentCommission ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">
                                Company
                            </p>
                            <p class="mt-2 text-lg font-black text-blue-700">
                                ₱{{ number_format($totalizatorCompanyCommission ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">
                                Pokémon Commission
                            </h2>
                            <p class="text-sm text-slate-500">
                                Commission generated from settled Pokémon battles.
                            </p>
                        </div>

                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black text-violet-700">
                            POKÉMON
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                                Bet
                            </p>
                            <p class="mt-2 text-lg font-black text-slate-900">
                                ₱{{ number_format($pokemonBetAmount ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">
                                Agent
                            </p>
                            <p class="mt-2 text-lg font-black text-emerald-700">
                                ₱{{ number_format($pokemonAgentCommission ?? 0, 2) }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-blue-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-blue-700">
                                Company
                            </p>
                            <p class="mt-2 text-lg font-black text-blue-700">
                                ₱{{ number_format($pokemonCompanyCommission ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Withdraw Summary --}}
            <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Approved Withdraw
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-emerald-600">
                        ₱{{ number_format($totalAgentApprovedWithdraw ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Pending Withdraw
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-yellow-600">
                        ₱{{ number_format($totalAgentPendingWithdraw ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Rejected Withdraw
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-red-600">
                        ₱{{ number_format($totalAgentRejectedWithdraw ?? 0, 2) }}
                    </h2>
                </div>
            </section>

            {{-- Wallet Summary --}}
            <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Total Wallet of Players
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-slate-900">
                        ₱{{ number_format($totalPlayerWallet ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Total Wallet of Agents
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-purple-600">
                        ₱{{ number_format($totalAgentWallet ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Total Wallet of Agents + Players
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-orange-600">
                        ₱{{ number_format($totalAgentAndPlayerWallet ?? 0, 2) }}
                    </h2>
                </div>
            </section>

            {{-- Totalizator Records --}}
            @if (($game ?? 'overall') === 'overall' || ($game ?? 'overall') === 'totalizator')
                <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 px-6 py-5">
                        <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                            <div>
                                <h2 class="text-xl font-black text-slate-900">
                                    Totalizator Commission Records
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Agent commission, company commission, and total commission from totalizator player bets.
                                </p>
                            </div>

                            <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600">
                                {{ ($commissions instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $commissions->total() : count($commissions ?? []) }} RECORDS
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1000px] text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-4">Date</th>
                                    <th class="px-5 py-4">Agent</th>
                                    <th class="px-5 py-4">Player</th>
                                    <th class="px-5 py-4 text-right">Bet Amount</th>
                                    <th class="px-5 py-4 text-right">Agent 3%</th>
                                    <th class="px-5 py-4 text-right">Company 2%</th>
                                    <th class="px-5 py-4 text-right">Total 5%</th>
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

                                        <td class="px-5 py-4">
                                            <span class="font-bold text-slate-900">
                                                {{ $commission->agent?->username ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4">
                                            {{ $commission->player?->username ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-slate-900">
                                            ₱{{ number_format($commission->bet_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-emerald-600">
                                            ₱{{ number_format($commission->commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-blue-600">
                                            ₱{{ number_format($commission->company_commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-red-600">
                                            ₱{{ number_format($commission->total_commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                                {{ strtoupper($commission->side ?? 'N/A') }}
                                            </span>
                                        </td>

                                        <td class="px-5 py-4 font-semibold text-slate-700">
                                            {{ $commission->odds ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-5 py-10 text-center text-slate-500">
                                            No totalizator commission records found.
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
                                    Pokémon Commission Records
                                </h2>

                                <p class="mt-1 text-sm text-slate-500">
                                    Pokémon game commission from settled battles.
                                </p>
                            </div>

                            <span class="w-fit rounded-full bg-violet-50 px-4 py-2 text-xs font-black text-violet-700">
                                {{ ($pokemonCommissions instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $pokemonCommissions->total() : count($pokemonCommissions ?? []) }} RECORDS
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                    <th class="px-5 py-4">Date</th>
                                    <th class="px-5 py-4">Event</th>
                                    <th class="px-5 py-4">Round</th>
                                    <th class="px-5 py-4 text-right">Bet Amount</th>
                                    <th class="px-5 py-4 text-right">Gross Win</th>
                                    <th class="px-5 py-4 text-right">Net Payout</th>
                                    <th class="px-5 py-4 text-right">Agent 3%</th>
                                    <th class="px-5 py-4 text-right">Company 2%</th>
                                    <th class="px-5 py-4 text-right">Total 5%</th>
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
                                            {{ $report->event_name ?? 'Pokemon Battle Room' }}
                                        </td>

                                        <td class="px-5 py-4">
                                            {{ $report->round_label ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-slate-900">
                                            ₱{{ number_format($report->total_bet_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-semibold text-slate-700">
                                            ₱{{ number_format($report->gross_payout_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-green-600">
                                            ₱{{ number_format($report->net_payout_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-emerald-600">
                                            ₱{{ number_format($report->agent_commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-blue-600">
                                            ₱{{ number_format($report->company_commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-red-600">
                                            ₱{{ number_format($report->commission_amount ?? 0, 2) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">
                                                {{ $report->status ?? 'N/A' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-5 py-10 text-center text-slate-500">
                                            No Pokémon commission records found.
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

            {{-- Withdraw Records --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                Agent Withdraw Report
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Only agent withdrawal requests are shown here. Player withdrawals are not included.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600">
                            WITHDRAWALS
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">Date</th>
                                <th class="px-5 py-4">Agent</th>
                                <th class="px-5 py-4 text-right">Amount</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Payment Method</th>
                                <th class="px-5 py-4">Account Name</th>
                                <th class="px-5 py-4">Account Number</th>
                                <th class="px-5 py-4">Note</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($agentWithdrawRequests as $withdraw)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $withdraw->created_at?->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        {{ $withdraw->user?->username ?? 'Unknown Agent' }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-red-600">
                                        ₱{{ number_format($withdraw->amount ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($withdraw->status === 'approved')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                                                Approved
                                            </span>
                                        @elseif ($withdraw->status === 'rejected')
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-black text-yellow-700">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $withdraw->payment_method ?? 'N/A' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $withdraw->account_name ?? 'N/A' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $withdraw->account_number ?? 'N/A' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $withdraw->note ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                                        No agent withdraw requests found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $agentWithdrawRequests->links() }}
                </div>
            </section>
        </main>
    </div>
</body>
</html>