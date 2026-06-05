<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    @php
        $currentRound = null;

        if ($openEvent) {
            $currentRound = \App\Models\GameRound::where('game_event_id', $openEvent->id)
                ->whereIn('status', ['open', 'closed'])
                ->latest()
                ->first();
        }
    @endphp

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Declare Dashboard
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Create an event, start a round, close betting, then declare the winner.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('declare.events.index') }}"
                   class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold transition">
                    Event History
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

    <main class="max-w-7xl mx-auto px-4 py-8">

        @if (session('success'))
            <div class="mb-4 rounded-xl bg-green-100 border border-green-200 text-green-700 px-5 py-4 font-semibold">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-xl bg-red-100 border border-red-200 text-red-700 px-5 py-4 font-semibold">
                {{ session('error') }}
            </div>
        @endif

        {{-- Event Control --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Event Control
                    </h2>

                    <p class="text-slate-500 mt-1">
                        Create one open event for the day. Rounds will be created inside this event.
                    </p>
                </div>

                @if ($openEvent)
                    <span class="inline-flex w-fit rounded-full bg-green-100 text-green-700 px-4 py-2 text-sm font-bold">
                        Event Open
                    </span>
                @else
                    <span class="inline-flex w-fit rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-bold">
                        No Open Event
                    </span>
                @endif
            </div>

            @if ($openEvent)
                <div class="mt-6 rounded-2xl bg-green-50 border border-green-200 p-5">
                    <p class="text-sm font-bold text-green-700">
                        Current Open Event
                    </p>

                    <h3 class="text-2xl font-extrabold text-green-900 mt-2">
                        {{ $openEvent->event_name }}
                    </h3>

                    <p class="text-green-800 mt-1">
                        Date: {{ $openEvent->event_date->format('M d, Y') }}
                    </p>

                    <div class="mt-4 flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('declare.events.show', $openEvent) }}"
                           class="inline-flex justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold transition">
                            View Event Matches
                        </a>

                        <form method="POST"
                              action="{{ route('declare.events.close', $openEvent) }}"
                              onsubmit="return confirm('Are you sure you want to close this event?')">
                            @csrf
                            @method('PATCH')

                            <button class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                                Close Event
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('declare.events.store') }}" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Event Name
                        </label>

                        <input
                            type="text"
                            name="event_name"
                            value="{{ old('event_name', 'Event ' . now()->format('M d, Y')) }}"
                            placeholder="Example: Event May 30, 2026"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">
                            Event Date
                        </label>

                        <input
                            type="date"
                            name="event_date"
                            value="{{ old('event_date', now()->format('Y-m-d')) }}"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div class="flex items-end">
                        <button class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 font-bold transition">
                            Create Event
                        </button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Live Event Video Section --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-5">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Live Event Video
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Watch the current match before closing betting and declaring the winner.
                    </p>
                </div>

                @if ($openEvent)
                    <span class="inline-flex w-fit rounded-full bg-green-100 text-green-700 px-4 py-2 text-sm font-bold">
                        {{ $openEvent->event_name }}
                    </span>
                @else
                    <span class="inline-flex w-fit rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-bold">
                        No Open Event
                    </span>
                @endif
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-black shadow">
                <div class="relative w-full aspect-video">
                    <iframe
                        class="absolute inset-0 w-full h-full"
                        src="https://www.youtube.com/embed/US1it7zjmvs"
                        title="Live Event Video"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>

                    {{--
                    If you want to use local video instead of iframe, remove the iframe above
                    and use this:

                    <video
                        class="absolute inset-0 w-full h-full"
                        src="{{ asset('videos/sample-video.mp4') }}"
                        controls
                        autoplay
                        muted
                        playsinline>
                    </video>
                    --}}
                </div>
            </div>
        </section>

        {{-- Round Control --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Round Control
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Start a new round, close betting for that round, then declare the winner.
                    </p>
                </div>

                @if ($currentRound)
                    @if ($currentRound->status === 'open')
                        <span class="rounded-full bg-green-100 text-green-700 px-4 py-2 text-sm font-bold">
                            Betting Open
                        </span>
                    @elseif ($currentRound->status === 'closed')
                        <span class="rounded-full bg-yellow-100 text-yellow-700 px-4 py-2 text-sm font-bold">
                            Betting Closed
                        </span>
                    @endif
                @else
                    <span class="rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-sm font-bold">
                        No Active Round
                    </span>
                @endif
            </div>

            <div class="mt-6">
                @if (! $openEvent)
                    <div class="rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
                        Create an event first before starting a round.
                    </div>
                @elseif (! $currentRound)
                    <form method="POST" action="{{ route('declare.rounds.start') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @csrf

                        <input
                            type="text"
                            name="round_code"
                            placeholder="Example: Round 1 / Fight #1"
                            class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >

                        <button class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold">
                            Start New Round
                        </button>
                    </form>
                @else
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">
                            Current Round
                        </p>

                        <h3 class="text-xl font-bold text-slate-900 mt-1">
                            {{ $currentRound->round_code ?? 'Round #' . $currentRound->id }}
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Status:
                            <span class="font-bold uppercase">
                                {{ $currentRound->status }}
                            </span>
                        </p>

                        @if ($currentRound->status === 'open')
                            <form
                                method="POST"
                                action="{{ route('declare.rounds.close', $currentRound) }}"
                                class="mt-4"
                                onsubmit="return confirm('Close betting for this round? Players will not be able to bet until the next round.')"
                            >
                                @csrf
                                @method('PATCH')

                                <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold">
                                    Close Betting
                                </button>
                            </form>
                        @elseif ($currentRound->status === 'closed')
                            <div class="mt-4 rounded-lg bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 text-sm">
                                Betting is closed. You can now declare the winner below.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        {{-- Declare Winner --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                Declare Winner
            </h2>

            @if (! $openEvent)
                <div class="mt-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
                    Create an event first before declaring a winner.
                </div>
            @elseif (! $currentRound)
                <div class="mt-3 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3">
                    Start a new round first.
                </div>
            @elseif ($currentRound->status === 'open')
                <div class="mt-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3">
                    Betting is still open. Close betting before declaring the winner.
                </div>
            @elseif ($currentRound->status === 'closed')
                <p class="text-slate-500 mt-1">
                    Current event:
                    <span class="font-bold text-slate-900">
                        {{ $openEvent->event_name }}
                    </span>
                </p>

                <p class="text-slate-500 mt-1">
                    Current round:
                    <span class="font-bold text-slate-900">
                        {{ $currentRound->round_code ?? 'Round #' . $currentRound->id }}
                    </span>
                </p>
            @endif

            <form method="POST" action="{{ route('declare.winner.store') }}" class="mt-6 space-y-5">
                @csrf

                <input type="hidden" name="game_round_id" value="{{ $currentRound?->id }}">

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Round Code / Fight Number
                    </label>

                    <input
                        type="text"
                        name="round_code"
                        value="{{ old('round_code', $currentRound?->round_code) }}"
                        placeholder="Example: Fight #1"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @disabled(! $currentRound || $currentRound->status !== 'closed')
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        name="winner"
                        value="MERON"
                        class="rounded-2xl bg-red-600 hover:bg-red-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-6 py-8 text-3xl font-extrabold transition"
                        @disabled(! $currentRound || $currentRound->status !== 'closed')
                    >
                        MERON
                    </button>

                    <button
                        name="winner"
                        value="WALA"
                        class="rounded-2xl bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-6 py-8 text-3xl font-extrabold transition"
                        @disabled(! $currentRound || $currentRound->status !== 'closed')
                    >
                        WALA
                    </button>

                    <button
                        name="winner"
                        value="DRAW"
                        class="rounded-2xl bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white px-6 py-8 text-3xl font-extrabold transition"
                        @disabled(! $currentRound || $currentRound->status !== 'closed')
                    >
                        DRAW
                    </button>
                </div>
            </form>
        </section>

        {{-- Declaration History --}}
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Declaration History
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Latest declared matches.
                    </p>
                </div>

                <a href="{{ route('declare.events.index') }}"
                   class="inline-flex w-fit rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-bold">
                    View All Events
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[800px]">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-left">Date</th>
                            <th class="px-5 py-4 text-left">Event</th>
                            <th class="px-5 py-4 text-left">Round</th>
                            <th class="px-5 py-4 text-left">Winner</th>
                            <th class="px-5 py-4 text-left">Declared By</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($declarations as $declaration)
                            <tr>
                                <td class="px-5 py-4">
                                    {{ $declaration->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $declaration->event?->event_name ?? 'No Event' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $declaration->round_code ?? 'N/A' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($declaration->winner === 'MERON')
                                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif ($declaration->winner === 'WALA')
                                        <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    {{ $declaration->declarer?->username ?? 'Unknown' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                    No declarations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $declarations->links() }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#16a34a'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

</body>
</html>