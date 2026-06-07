<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sales Reports</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        {{-- Top Navigation --}}
        <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900">
                        Sales Reports
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        View daily and event-based reports for Totalizator, Pokémon, and overall sales.
                    </p>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    Back
                </a>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Filter --}}
            <section class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-7">

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Game
                            </label>

                            <select name="game"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="overall" {{ $game === 'overall' ? 'selected' : '' }}>
                                    Overall
                                </option>

                                <option value="totalizator" {{ $game === 'totalizator' ? 'selected' : '' }}>
                                    Totalizator
                                </option>

                                <option value="pokemon" {{ $game === 'pokemon' ? 'selected' : '' }}>
                                    Pokémon Game
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Report Type
                            </label>

                            <select name="report_type"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="daily" {{ $reportType === 'daily' ? 'selected' : '' }}>
                                    Daily Sales
                                </option>

                                <option value="event" {{ $reportType === 'event' ? 'selected' : '' }}>
                                    Event Sales
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Event
                            </label>

                            <select name="event_key"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">
                                    All Events
                                </option>

                                @foreach ($allEvents as $filterEvent)
                                    @php
                                        $key = $filterEvent->source_game . '|' . $filterEvent->source_id;
                                    @endphp

                                    <option value="{{ $key }}" @selected((string) $eventKey === (string) $key)>
                                        {{ $filterEvent->event_name }} - {{ strtoupper($filterEvent->source_game) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                From
                            </label>

                            <input type="date"
                                   name="from"
                                   value="{{ $from }}"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                To
                            </label>

                            <input type="date"
                                   name="to"
                                   value="{{ $to }}"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                                Search
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   placeholder="Search event..."
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                        </div>

                        <div class="flex items-end">
                            <button class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Summary --}}
            <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Records
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-slate-900">
                        {{ number_format($summary->total_records ?? 0) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-yellow-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Total Bets
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-yellow-600">
                        ₱{{ number_format($summary->total_bets ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Gross Payout
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-slate-900">
                        ₱{{ number_format($summary->gross_payout ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-green-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Net Payout
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-green-600">
                        ₱{{ number_format($summary->net_payout ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-red-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Total Commission
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-red-600">
                        ₱{{ number_format($summary->total_commission ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-blue-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Commission
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-blue-600">
                        ₱{{ number_format($summary->agent_commission ?? 0, 2) }}
                    </h2>
                </div>

                <div class="rounded-3xl border border-purple-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Company Commission
                    </p>

                    <h2 class="mt-3 text-2xl font-black text-purple-600">
                        ₱{{ number_format($summary->company_commission ?? 0, 2) }}
                    </h2>
                </div>

            </section>

            {{-- Report Table --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                {{ $reportType === 'event' ? 'Event Sales Report' : 'Daily Sales Report' }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Detailed report based on your selected filters.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase text-slate-700">
                                {{ strtoupper($game) }}
                            </span>

                            <span class="rounded-full bg-blue-50 px-4 py-2 text-xs font-black uppercase text-blue-700">
                                {{ strtoupper($reportType) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] text-sm">
                        <thead>
                            <tr class="bg-slate-50 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">
                                    {{ $reportType === 'event' ? 'Event' : 'Date' }}
                                </th>
                                <th class="px-5 py-4">
                                    Game
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Records
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Total Bets
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Gross Payout
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Net Payout
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Total Commission
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Agent
                                </th>
                                <th class="px-5 py-4 text-right">
                                    Company
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @forelse ($reports as $row)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="px-5 py-4 font-bold text-slate-900">
                                        @if ($reportType === 'event')
                                            {{ $row->event_name ?? 'N/A' }}
                                        @else
                                            {{ $row->report_date }}
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($row->source_game === 'pokemon')
                                            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-black uppercase text-violet-700">
                                                Pokémon
                                            </span>
                                        @elseif ($row->source_game === 'totalizator')
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">
                                                Totalizator
                                            </span>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700">
                                                {{ $row->source_game }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-right font-semibold text-slate-700">
                                        {{ number_format($row->total_records ?? 0) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-yellow-600">
                                        ₱{{ number_format($row->total_bets ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-semibold text-slate-700">
                                        ₱{{ number_format($row->gross_payout ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-green-600">
                                        ₱{{ number_format($row->net_payout ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-red-600">
                                        ₱{{ number_format($row->total_commission ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-blue-600">
                                        ₱{{ number_format($row->agent_commission ?? 0, 2) }}
                                    </td>

                                    <td class="px-5 py-4 text-right font-black text-purple-600">
                                        ₱{{ number_format($row->company_commission ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-12 text-center">
                                        <div class="mx-auto max-w-md">
                                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                                                <span class="text-xl font-black text-slate-400">?</span>
                                            </div>

                                            <h3 class="text-base font-black text-slate-800">
                                                No reports found
                                            </h3>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Try changing the date range, game type, report type, or search filter.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>