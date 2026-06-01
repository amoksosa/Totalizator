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
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold">
                Declare Events
            </h1>

            <a href="{{ route('declare.dashboard') }}"
               class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                Dashboard
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

        <section class="bg-white rounded-xl shadow p-5 mb-6">
            <h2 class="text-lg font-bold mb-4">
                Create Event
            </h2>

            @if ($openEvent)
                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">
                    <p class="font-semibold text-yellow-800">
                        You currently have an open event:
                    </p>

                    <p class="mt-1">
                        {{ $openEvent->event_name }} — {{ $openEvent->event_date->format('M d, Y') }}
                    </p>

                    <a href="{{ route('declare.events.show', $openEvent) }}"
                       class="inline-block mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        View Event
                    </a>
                </div>
            @else
                <form method="POST" action="{{ route('declare.events.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @csrf

                    <input
                        type="text"
                        name="event_name"
                        placeholder="Event name"
                        class="border border-slate-300 rounded-lg px-4 py-2"
                        required
                    >

                    <input
                        type="date"
                        name="event_date"
                        value="{{ now()->format('Y-m-d') }}"
                        class="border border-slate-300 rounded-lg px-4 py-2"
                        required
                    >

                    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold">
                        Create Event
                    </button>
                </form>
            @endif
        </section>

        <section class="bg-white rounded-xl shadow overflow-hidden">
            <div class="p-5 border-b border-slate-200">
                <h2 class="text-lg font-bold">
                    Event History
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead class="bg-slate-100">
                        <tr>
                            <th class="p-3 text-left">Event</th>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Matches</th>
                            <th class="p-3 text-left">Created</th>
                            <th class="p-3 text-left">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($events as $event)
                            <tr class="border-t">
                                <td class="p-3 font-semibold">
                                    {{ $event->event_name }}
                                </td>

                                <td class="p-3">
                                    {{ $event->event_date->format('M d, Y') }}
                                </td>

                                <td class="p-3">
                                    @if ($event->status === 'open')
                                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                            Open
                                        </span>
                                    @else
                                        <span class="bg-slate-200 text-slate-700 px-3 py-1 rounded-full text-xs font-bold">
                                            Closed
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    {{ $event->declarations_count }}
                                </td>

                                <td class="p-3">
                                    {{ $event->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="p-3">
                                    <a href="{{ route('declare.events.show', $event) }}"
                                       class="bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-semibold">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-500">
                                    No events yet.
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