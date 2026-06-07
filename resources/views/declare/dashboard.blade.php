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

        $canStartRound = $openEvent && ! $currentRound;
        $canCloseBetting = $currentRound && $currentRound->status === 'open';
        $canDeclareWinner = $currentRound && $currentRound->status === 'closed';
    @endphp

    <nav class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                    Declare Dashboard
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage live events, control rounds, close betting, and declare winners.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('declare.events.index') }}"
                   class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                    Event History
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-6">

        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Top Status Cards --}}
        <section class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                    Event
                </p>

                @if ($openEvent)
                    <h2 class="mt-2 text-lg font-black text-slate-900">
                        {{ $openEvent->event_name }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $openEvent->event_date->format('M d, Y') }}
                    </p>

                    <span class="mt-4 inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                        Open
                    </span>
                @else
                    <h2 class="mt-2 text-lg font-black text-slate-900">
                        No Open Event
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Create an event first.
                    </p>

                    <span class="mt-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        Waiting
                    </span>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                    Current Round
                </p>

                @if ($currentRound)
                    <h2 class="mt-2 text-lg font-black text-slate-900">
                        {{ $currentRound->round_code ?? 'Round #' . $currentRound->id }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Status: {{ ucfirst($currentRound->status) }}
                    </p>

                    @if ($currentRound->status === 'open')
                        <span class="mt-4 inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                            Betting Open
                        </span>
                    @else
                        <span class="mt-4 inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-black text-yellow-700">
                            Betting Closed
                        </span>
                    @endif
                @else
                    <h2 class="mt-2 text-lg font-black text-slate-900">
                        No Active Round
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Start a round after creating an event.
                    </p>

                    <span class="mt-4 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        Waiting
                    </span>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                    Next Action
                </p>

                @if (! $openEvent)
                    <h2 class="mt-2 text-lg font-black text-blue-700">
                        Create Event
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Start by creating today’s event.
                    </p>
                @elseif (! $currentRound)
                    <h2 class="mt-2 text-lg font-black text-blue-700">
                        Start Round
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Open betting by starting a round.
                    </p>
                @elseif ($currentRound->status === 'open')
                    <h2 class="mt-2 text-lg font-black text-red-700">
                        Close Betting
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Close betting before declaring winner.
                    </p>
                @else
                    <h2 class="mt-2 text-lg font-black text-emerald-700">
                        Declare Winner
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Choose MERON, WALA, or DRAW.
                    </p>
                @endif
            </div>
        </section>

        {{-- Main Layout --}}
        <section class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-[1fr_420px]">

            {{-- Live Video --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">
                                Live Event Video
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Watch the match before closing betting and declaring the winner.
                            </p>
                        </div>

                        @if ($openEvent)
                            <span class="w-fit rounded-full bg-green-100 px-3 py-1 text-xs font-black text-green-700">
                                {{ $openEvent->event_name }}
                            </span>
                        @else
                            <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                                No Open Event
                            </span>
                        @endif
                    </div>
                </div>

                <div class="bg-black">
                    <div class="relative aspect-video w-full">
                        <iframe
                            class="absolute inset-0 h-full w-full"
                            src="https://www.youtube.com/embed/US1it7zjmvs"
                            title="Live Event Video"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>

            {{-- Step Controls --}}
            <div class="space-y-5">

                {{-- Step 1: Event --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-sm font-black text-blue-700">
                            1
                        </div>

                        <div>
                            <h2 class="font-black text-slate-900">
                                Event Control
                            </h2>

                            <p class="text-xs text-slate-500">
                                Create or close the current event.
                            </p>
                        </div>
                    </div>

                    @if ($openEvent)
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                            <p class="text-xs font-black uppercase text-green-700">
                                Current Open Event
                            </p>

                            <h3 class="mt-2 font-black text-slate-900">
                                {{ $openEvent->event_name }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-600">
                                {{ $openEvent->event_date->format('M d, Y') }}
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-2">
                                <a href="{{ route('declare.events.show', $openEvent) }}"
                                   class="inline-flex justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                                    View Event Matches
                                </a>

                                <form method="POST"
                                      action="{{ route('declare.events.close', $openEvent) }}"
                                      onsubmit="return confirm('Are you sure you want to close this event?')">
                                    @csrf
                                    @method('PATCH')

                                    <button class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                                        Close Event
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('declare.events.store') }}" class="space-y-3">
                            @csrf

                            <input
                                type="text"
                                name="event_name"
                                value="{{ old('event_name', 'Event ' . now()->format('M d, Y')) }}"
                                placeholder="Event name"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >

                            <input
                                type="date"
                                name="event_date"
                                value="{{ old('event_date', now()->format('Y-m-d')) }}"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >

                            <button class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                                Create Event
                            </button>
                        </form>
                    @endif
                </div>

                {{-- Step 2: Round --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-100 text-sm font-black text-purple-700">
                            2
                        </div>

                        <div>
                            <h2 class="font-black text-slate-900">
                                Round Control
                            </h2>

                            <p class="text-xs text-slate-500">
                                Start a round and close betting.
                            </p>
                        </div>
                    </div>

                    @if (! $openEvent)
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                            Create an event first before starting a round.
                        </div>
                    @elseif (! $currentRound)
                        <form method="POST" action="{{ route('declare.rounds.start') }}" class="space-y-3">
                            @csrf

                            <input
                                type="text"
                                name="round_code"
                                placeholder="Example: Round 1 / Fight #1"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-500"
                            >

                            <button class="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                                Start New Round
                            </button>
                        </form>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-black uppercase text-slate-400">
                                Current Round
                            </p>

                            <h3 class="mt-2 font-black text-slate-900">
                                {{ $currentRound->round_code ?? 'Round #' . $currentRound->id }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                Status:
                                <span class="font-black uppercase text-slate-900">
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

                                    <button class="w-full rounded-xl bg-red-600 px-4 py-3 text-sm font-black text-white transition hover:bg-red-700">
                                        Close Betting
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                                    Betting is closed. You can declare the winner.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            </div>
        </section>

        {{-- Declare Winner --}}
        <section class="mb-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-sm font-black text-emerald-700">
                            3
                        </div>

                        <div>
                            <h2 class="text-lg font-black text-slate-900">
                                Declare Winner
                            </h2>

                            <p class="text-sm text-slate-500">
                                Choose the winning side after betting is closed.
                            </p>
                        </div>
                    </div>
                </div>

                @if ($canDeclareWinner)
                    <span class="w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                        Ready to Declare
                    </span>
                @else
                    <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                        Not Ready
                    </span>
                @endif
            </div>

            @if (! $openEvent)
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                    Create an event first before declaring a winner.
                </div>
            @elseif (! $currentRound)
                <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm font-semibold text-yellow-800">
                    Start a new round first.
                </div>
            @elseif ($currentRound->status === 'open')
                <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
                    Betting is still open. Close betting before declaring the winner.
                </div>
            @endif

            <form method="POST" action="{{ route('declare.winner.store') }}" class="mt-5 space-y-4">
                @csrf

                <input type="hidden" name="game_round_id" value="{{ $currentRound?->id }}">

                <div>
                    <label class="mb-2 block text-sm font-black text-slate-700">
                        Round Code / Fight Number
                    </label>

                    <input
                        type="text"
                        name="round_code"
                        value="{{ old('round_code', $currentRound?->round_code) }}"
                        placeholder="Example: Fight #1"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100 disabled:text-slate-400"
                        @disabled(! $canDeclareWinner)
                    >
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <button
                        name="winner"
                        value="MERON"
                        class="rounded-2xl bg-red-600 px-6 py-7 text-3xl font-black text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @disabled(! $canDeclareWinner)
                    >
                        MERON
                    </button>

                    <button
                        name="winner"
                        value="WALA"
                        class="rounded-2xl bg-blue-600 px-6 py-7 text-3xl font-black text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @disabled(! $canDeclareWinner)
                    >
                        WALA
                    </button>

                    <button
                        name="winner"
                        value="DRAW"
                        class="rounded-2xl bg-emerald-600 px-6 py-7 text-3xl font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @disabled(! $canDeclareWinner)
                    >
                        DRAW
                    </button>
                </div>
            </form>
        </section>

        {{-- Declaration History --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-slate-900">
                        Declaration History
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Latest declared matches.
                    </p>
                </div>

                <a href="{{ route('declare.events.index') }}"
                   class="inline-flex w-fit rounded-xl bg-slate-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-900">
                    View All Events
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Round</th>
                            <th class="px-4 py-3 text-left">Winner</th>
                            <th class="px-4 py-3 text-left">Declared By</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($declarations as $declaration)
                            <tr class="transition hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    {{ $declaration->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-4 py-4 font-bold text-slate-900">
                                    {{ $declaration->event?->event_name ?? 'No Event' }}
                                </td>

                                <td class="px-4 py-4">
                                    {{ $declaration->round_code ?? 'N/A' }}
                                </td>

                                <td class="px-4 py-4">
                                    @if ($declaration->winner === 'MERON')
                                        <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-black text-red-700">
                                            MERON
                                        </span>
                                    @elseif ($declaration->winner === 'WALA')
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-black text-blue-700">
                                            WALA
                                        </span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4">
                                    {{ $declaration->declarer?->username ?? 'Unknown' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <h3 class="text-lg font-black text-slate-900">
                                        No declarations yet
                                    </h3>

                                    <p class="mt-2 text-sm text-slate-500">
                                        Declared matches will appear here.
                                    </p>
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