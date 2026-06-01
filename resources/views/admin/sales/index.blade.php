<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Sales Report
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    View sales, bets, commissions, payouts, and net report per event.
                </p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold">
                Dashboard
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
            <form method="GET" action="{{ route('admin.sales.index') }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-3">

                <select
                    name="event_id"
                    class="rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Events</option>

                    @foreach ($allEvents as $filterEvent)
                        <option value="{{ $filterEvent->id }}" @selected((string) request('event_id') === (string) $filterEvent->id)>
                            {{ $filterEvent->event_name }} — {{ $filterEvent->event_date?->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search event name..."
                    class="rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <select
                    name="status"
                    class="rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="closed" @selected(request('status') === 'closed')>Closed</option>
                </select>

                <div class="grid grid-cols-2 gap-2">
                    <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold">
                        Filter
                    </button>

                    <a href="{{ route('admin.sales.index') }}"
                       class="rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </section>

        <section class="space-y-5">
            @forelse ($events as $event)
                @php
                    $totals = $eventTotals[$event->id] ?? [
                        'total_bets' => 0,
                        'total_commission' => 0,
                        'total_company_commission' => 0,
                        'total_agent_commission' => 0,
                        'total_payout_amount' => 0,
                        'net_after_payout' => 0,
                        'net_sales_before_payout' => 0,
                    ];
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="p-6 border-b border-slate-200 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900">
                                {{ $event->event_name }}
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $event->event_date?->format('M d, Y') }}
                                —
                                Created by: {{ $event->creator?->username ?? 'Unknown' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if ($event->status === 'open')
                                <span class="rounded-full bg-green-100 text-green-700 px-4 py-2 text-xs font-bold">
                                    OPEN
                                </span>
                            @else
                                <span class="rounded-full bg-slate-200 text-slate-700 px-4 py-2 text-xs font-bold">
                                    CLOSED
                                </span>
                            @endif

                            <a href="{{ route('admin.sales.show', $event) }}"
                               class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold">
                                View Report
                            </a>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 p-6">
                        <div class="rounded-xl bg-blue-50 border border-blue-200 p-4">
                            <p class="text-sm font-bold text-blue-700">Total Bets</p>
                            <h3 class="text-2xl font-extrabold text-blue-900 mt-2">
                                ₱{{ number_format($totals['total_bets'], 2) }}
                            </h3>
                        </div>

                        <div class="rounded-xl bg-purple-50 border border-purple-200 p-4">
                            <p class="text-sm font-bold text-purple-700">Total Commission</p>
                            <h3 class="text-2xl font-extrabold text-purple-900 mt-2">
                                ₱{{ number_format($totals['total_commission'], 2) }}
                            </h3>
                        </div>

                        <div class="rounded-xl bg-emerald-50 border border-emerald-200 p-4">
                            <p class="text-sm font-bold text-emerald-700">Company 2%</p>
                            <h3 class="text-2xl font-extrabold text-emerald-900 mt-2">
                                ₱{{ number_format($totals['total_company_commission'], 2) }}
                            </h3>
                        </div>

                        <div class="rounded-xl bg-orange-50 border border-orange-200 p-4">
                            <p class="text-sm font-bold text-orange-700">Payout</p>
                            <h3 class="text-2xl font-extrabold text-orange-900 mt-2">
                                ₱{{ number_format($totals['total_payout_amount'], 2) }}
                            </h3>
                        </div>

                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">
                            <p class="text-sm font-bold text-slate-700">Net After Payout</p>
                            <h3 class="text-2xl font-extrabold {{ $totals['net_after_payout'] >= 0 ? 'text-green-700' : 'text-red-700' }} mt-2">
                                ₱{{ number_format($totals['net_after_payout'], 2) }}
                            </h3>
                        </div>
                    </div>

                    <div class="px-6 pb-6 grid grid-cols-1 md:grid-cols-4 gap-3 text-sm">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-slate-500">Matches</p>
                            <p class="font-bold text-slate-900 mt-1">
                                {{ $event->declarations_count }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-slate-500">Bet Records</p>
                            <p class="font-bold text-slate-900 mt-1">
                                {{ $event->bets_count }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-slate-500">Agent 3%</p>
                            <p class="font-bold text-slate-900 mt-1">
                                ₱{{ number_format($totals['total_agent_commission'], 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <p class="text-slate-500">Net Before Payout</p>
                            <p class="font-bold text-slate-900 mt-1">
                                ₱{{ number_format($totals['net_sales_before_payout'], 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-12 text-center">
                    <h2 class="text-xl font-bold text-slate-900">
                        No events found
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Sales reports will appear here after declare creates events.
                    </p>
                </div>
            @endforelse
        </section>

        <div class="mt-6">
            {{ $events->links() }}
        </div>

    </main>
</body>
</html>