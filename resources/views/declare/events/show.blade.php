<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event History</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    {{ $event->event_name }}
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    {{ $event->event_date->format('M d, Y') }} —
                    <span class="font-semibold capitalize">
                        {{ $event->status }}
                    </span>
                </p>
            </div>

            <a href="{{ route('declare.events.index') }}"
               class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-semibold transition">
                Back
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Status
                </p>

                <div class="mt-2">
                    @if ($event->status === 'open')
                        <span class="inline-flex rounded-full bg-green-100 text-green-700 px-4 py-1.5 text-sm font-bold">
                            Open
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-slate-200 text-slate-700 px-4 py-1.5 text-sm font-bold">
                            Closed
                        </span>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Total Matches
                </p>

                <h2 class="text-3xl font-bold text-slate-900 mt-1">
                    {{ $event->declarations->count() }}
                </h2>
            </div>

            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Closed At
                </p>

                <h2 class="text-base font-bold text-slate-900 mt-2">
                    {{ $event->closed_at ? $event->closed_at->format('M d, Y h:i A') : 'Not closed yet' }}
                </h2>
            </div>
        </section>

        @if ($event->status === 'open')
            <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Close Event
                        </h2>

                        <p class="text-sm text-slate-500 mt-1">
                            Closing this event will mark it as completed.
                        </p>
                    </div>

                    <form method="POST"
                          action="{{ route('declare.events.close', $event) }}"
                          onsubmit="return confirm('Are you sure you want to close this event?')">
                        @csrf
                        @method('PATCH')

                        <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                            Close Event
                        </button>
                    </form>
                </div>
            </section>
        @endif

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">
                    Match History Inside This Event
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    All declared rounds and winners for this event.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Round Code</th>
                            <th class="px-4 py-3 text-left">Winner</th>
                            <th class="px-4 py-3 text-left">Declared By</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($event->declarations as $declaration)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    {{ $declaration->created_at?->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-bold">
                                        {{ $declaration->round_code ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4">
                                    @if (strtoupper($declaration->winner ?? '') === 'MERON')
                                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif (strtoupper($declaration->winner ?? '') === 'WALA')
                                        <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @elseif (strtoupper($declaration->winner ?? '') === 'DRAW')
                                        <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-bold">
                                            {{ strtoupper($declaration->winner ?? 'N/A') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 font-semibold text-slate-800">
                                    {{ $declaration->declarer?->username ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <h3 class="text-lg font-bold text-slate-900">
                                        No matches recorded yet
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Match declarations for this event will appear here.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</body>
</html>