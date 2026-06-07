<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Events</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Declare Events
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Create events, manage open events, and review event history.
                </p>
            </div>

            <a href="{{ route('declare.dashboard') }}"
               class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-semibold transition">
                Dashboard
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

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 mb-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Create Event
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Only one event can stay open at a time.
                    </p>
                </div>
            </div>

            @if ($openEvent)
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-5">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-bold text-yellow-800">
                                You currently have an open event
                            </p>

                            <h3 class="mt-2 text-xl font-bold text-slate-900">
                                {{ $openEvent->event_name }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-600">
                                Event Date:
                                <span class="font-semibold">
                                    {{ $openEvent->event_date->format('M d, Y') }}
                                </span>
                            </p>
                        </div>

                        <a href="{{ route('declare.events.show', $openEvent) }}"
                           class="inline-flex items-center justify-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold transition">
                            View Event
                        </a>
                    </div>
                </div>
            @else
                <form method="POST"
                      action="{{ route('declare.events.store') }}"
                      class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3">
                    @csrf

                    <input
                        type="text"
                        name="event_name"
                        placeholder="Event name"
                        class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >

                    <input
                        type="date"
                        name="event_date"
                        value="{{ now()->format('Y-m-d') }}"
                        class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >

                    <button class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 text-sm font-bold transition">
                        Create Event
                    </button>
                </form>
            @endif
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">
                    Event History
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    View created events, status, and declared matches.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Matches</th>
                            <th class="px-4 py-3 text-left">Created</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($events as $event)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $event->event_name }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        Event ID: {{ $event->id }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    {{ $event->event_date->format('M d, Y') }}
                                </td>

                                <td class="px-4 py-4">
                                    @if ($event->status === 'open')
                                        <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            Open
                                        </span>
                                    @else
                                        <span class="rounded-full bg-slate-200 text-slate-700 px-3 py-1 text-xs font-bold">
                                            Closed
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right font-bold text-slate-900">
                                    {{ $event->declarations_count }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ $event->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-4 py-4">
                                    <a href="{{ route('declare.events.show', $event) }}"
                                       class="inline-flex rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-xs font-bold transition">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <h3 class="text-lg font-bold text-slate-900">
                                        No events yet
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Create your first event above.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">
            {{ $events->links() }}
        </div>

    </main>

</body>
</html>