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
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">
                    {{ $event->event_name }}
                </h1>

                <p class="text-sm text-slate-500">
                    {{ $event->event_date->format('M d, Y') }} —
                    {{ ucfirst($event->status) }}
                </p>
            </div>

            <a href="{{ route('declare.events.index') }}"
               class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Back
            </a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-6">

        @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 text-green-700 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-100 text-red-700 px-4 py-3">
                {{ session('error') }}
            </div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-slate-500">Status</p>
                <h2 class="text-2xl font-bold mt-1">
                    {{ ucfirst($event->status) }}
                </h2>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-slate-500">Total Matches</p>
                <h2 class="text-2xl font-bold mt-1">
                    {{ $event->declarations->count() }}
                </h2>
            </div>

            <div class="bg-white rounded-xl shadow p-5">
                <p class="text-sm text-slate-500">Closed At</p>
                <h2 class="text-lg font-bold mt-1">
                    {{ $event->closed_at ? $event->closed_at->format('M d, Y h:i A') : 'Not closed yet' }}
                </h2>
            </div>
        </section>

        @if ($event->status === 'open')
            <form method="POST"
                  action="{{ route('declare.events.close', $event) }}"
                  class="mb-6"
                  onsubmit="return confirm('Are you sure you want to close this event?')">
                @csrf
                @method('PATCH')

                <button class="bg-red-600 hover:bg-red-700 text-white px-5 py-3 rounded-lg font-semibold">
                    Close Event
                </button>
            </form>
        @endif

        <section class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-5 border-b border-slate-200">
                <h2 class="text-lg font-bold">
                    Match History Inside This Event
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Round Code</th>
                            <th class="p-3 text-left">Winner</th>
                            <th class="p-3 text-left">Declared By</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($event->declarations as $declaration)
                            <tr class="border-t">
                                <td class="p-3">
                                    {{ $declaration->created_at?->format('M d, Y h:i A') }}
                                </td>

                                <td class="p-3 font-semibold">
                                    {{ $declaration->round_code ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    {{ strtoupper($declaration->winner ?? 'N/A') }}
                                </td>

                                <td class="p-3">
                                    {{ $declaration->declarer?->username ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-6 text-center text-slate-500">
                                    No matches recorded in this event yet.
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