<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Sales Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        {{-- Top Navigation --}}
        <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:px-8 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-blue-700">
                            Event Report
                        </span>

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-600">
                            {{ $event->event_date->format('M d, Y') }}
                        </span>
                    </div>

                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 md:text-3xl">
                        {{ $event->event_name }}
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Detailed sales, betting, payout, and commission report for this event.
                    </p>
                </div>

                <a href="{{ route('admin.sales.index') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    Back to Reports
                </a>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Main Summary --}}
            <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">

                <div class="rounded-3xl border border-blue-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Bets
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-blue-700">
                                ₱{{ number_format($totals['total_bets'], 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-blue-50 px-3 py-2 text-xs font-black text-blue-700">
                            BETS
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
                                ₱{{ number_format($totals['total_commission'], 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-red-50 px-3 py-2 text-xs font-black text-red-700">
                            5%
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Company Commission 2%
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-emerald-600">
                                ₱{{ number_format($totals['total_company_commission'], 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700">
                            2%
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-orange-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Total Payout
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight text-orange-600">
                                ₱{{ number_format($totals['total_payout_amount'], 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-orange-50 px-3 py-2 text-xs font-black text-orange-700">
                            PAYOUT
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border {{ $totals['net_after_payout'] >= 0 ? 'border-green-100' : 'border-red-100' }} bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">
                                Net After Payout
                            </p>

                            <h2 class="mt-3 text-3xl font-black tracking-tight {{ $totals['net_after_payout'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₱{{ number_format($totals['net_after_payout'], 2) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl {{ $totals['net_after_payout'] >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }} px-3 py-2 text-xs font-black">
                            NET
                        </div>
                    </div>
                </div>

            </section>

            {{-- Secondary Summary --}}
            <section class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Commission 3%
                    </p>

                    <h3 class="mt-3 text-2xl font-black text-blue-600">
                        ₱{{ number_format($totals['total_agent_commission'], 2) }}
                    </h3>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Pending Bets
                    </p>

                    <h3 class="mt-3 text-2xl font-black text-yellow-600">
                        ₱{{ number_format($totals['pending_bets'], 2) }}
                    </h3>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Won Bet Amount
                    </p>

                    <h3 class="mt-3 text-2xl font-black text-green-600">
                        ₱{{ number_format($totals['won_bets'], 2) }}
                    </h3>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Lost Bet Amount
                    </p>

                    <h3 class="mt-3 text-2xl font-black text-red-600">
                        ₱{{ number_format($totals['lost_bets'], 2) }}
                    </h3>
                </div>

            </section>

            {{-- Match History --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                Match History
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Declared matches and winners for this event.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600">
                            {{ $event->declarations->count() }} MATCHES
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">Date</th>
                                <th class="px-5 py-4">Round</th>
                                <th class="px-5 py-4">Winner</th>
                                <th class="px-5 py-4">Declared By</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($event->declarations as $declaration)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $declaration->created_at?->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                            {{ $declaration->round_code ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4">
                                        @if (strtoupper($declaration->winner) === 'MERON')
                                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">
                                                MERON
                                            </span>
                                        @elseif (strtoupper($declaration->winner) === 'WALA')
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                                WALA
                                            </span>
                                        @elseif (strtoupper($declaration->winner) === 'DRAW')
                                            <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-black text-yellow-700">
                                                DRAW
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                                {{ $declaration->winner }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $declaration->declarer?->username ?? 'Unknown' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                                        No matches declared in this event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Player Bets --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                Player Bets
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                List of player bets recorded under this event.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-blue-50 px-4 py-2 text-xs font-black text-blue-700">
                            {{ ($bets instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $bets->total() : count($bets ?? []) }} BETS
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">Date</th>
                                <th class="px-5 py-4">Player</th>
                                <th class="px-5 py-4">Agent</th>
                                <th class="px-5 py-4">Side</th>
                                <th class="px-5 py-4">Odds</th>
                                <th class="px-5 py-4 text-right">Amount</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Payout</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($bets as $bet)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $bet->created_at?->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        {{ $bet->user?->username ?? 'Unknown' }}
                                    </td>

                                    <td class="px-5 py-4 text-slate-700">
                                        {{ $bet->user?->agent?->username ?? 'None' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if (strtoupper($bet->side) === 'MERON')
                                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700">
                                                MERON
                                            </span>
                                        @elseif (strtoupper($bet->side) === 'WALA')
                                            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-black text-blue-700">
                                                WALA
                                            </span>
                                        @elseif (strtoupper($bet->side) === 'DRAW')
                                            <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-black text-yellow-700">
                                                DRAW
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">
                                                {{ $bet->side }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $bet->odds }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-slate-900">
                                        ₱{{ number_format($bet->amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($bet->status === 'won')
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                                                Won
                                            </span>
                                        @elseif ($bet->status === 'lost')
                                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                                                Lost
                                            </span>
                                        @elseif ($bet->status === 'refunded')
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700">
                                                Refunded
                                            </span>
                                        @else
                                            <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-black text-yellow-700">
                                                {{ ucfirst($bet->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right font-black {{ ($bet->payout_amount ?? 0) > 0 ? 'text-green-600' : 'text-slate-700' }}">
                                        ₱{{ number_format($bet->payout_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                                        No bets found for this event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $bets->links() }}
                </div>
            </section>

            {{-- Commission Details --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                Commission Details
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Breakdown of agent, company, and total commission records.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-emerald-50 px-4 py-2 text-xs font-black text-emerald-700">
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
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($commissions as $commission)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-semibold text-slate-700">
                                        {{ $commission->created_at?->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        {{ $commission->agent?->username ?? 'Unknown' }}
                                    </td>

                                    <td class="px-5 py-4 text-slate-700">
                                        {{ $commission->player?->username ?? 'Unknown' }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-slate-900">
                                        ₱{{ number_format($commission->bet_amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-blue-600">
                                        ₱{{ number_format($commission->commission_amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-emerald-600">
                                        ₱{{ number_format($commission->company_commission_amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-red-600">
                                        ₱{{ number_format($commission->total_commission_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                                        No commission records for this event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-200 px-6 py-4">
                    {{ $commissions->links() }}
                </div>
            </section>

        </main>
    </div>

</body>
</html>