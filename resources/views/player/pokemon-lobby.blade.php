<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon PvP Lobby</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#1d4ed8_0,#020617_42%,#020617_100%)]">

        {{-- Navbar --}}
        <nav class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/85 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">
                            Pokémon PvP Lobby
                        </h1>

                        <p class="mt-1 text-sm text-slate-400">
                            Create a battle room or join another player’s lobby.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">

                        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 shadow-lg shadow-emerald-950/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-lg">
                                    💰
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wide text-emerald-300">
                                        Balance
                                    </p>

                                    <p class="text-sm font-black text-white">
                                        ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('player.dashboard') }}"
                           class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-black text-white transition hover:bg-white/20">
                            Dashboard
                        </a>

                    </div>

                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8">

            {{-- Alerts --}}
            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-green-400/20 bg-green-500/10 px-5 py-4 text-sm font-bold text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-400/20 bg-red-500/10 px-5 py-4 text-sm font-bold text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Hero --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur">
                <div class="relative p-6 md:p-8">
                    <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-56 w-56 rounded-full bg-yellow-400/10 blur-3xl"></div>

                    <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px] lg:items-center">
                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-blue-300">
                                PvP Battle Arena
                            </p>

                            <h2 class="mt-3 text-4xl font-black text-white md:text-5xl">
                                Battle Other Players
                            </h2>

                            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 md:text-base">
                                Create a lobby with your bet amount, wait for another player to join, choose your Pokémon,
                                and fight for the pot.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-yellow-300/20 bg-yellow-400/10 p-5">
                            <p class="text-sm font-bold text-yellow-200">
                                How it works
                            </p>

                            <div class="mt-4 space-y-3 text-sm text-slate-200">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/20 text-xs font-black text-blue-200">1</span>
                                    <span>Create or join a lobby</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/20 text-xs font-black text-blue-200">2</span>
                                    <span>Choose your Pokémon</span>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-500/20 text-xs font-black text-blue-200">3</span>
                                    <span>Ready up and battle</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Create Lobby --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl backdrop-blur">
                <div class="border-b border-white/10 px-6 py-5">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-2xl font-black text-white">
                                Create PvP Lobby
                            </h2>

                            <p class="mt-1 text-sm text-slate-400">
                                Set your bet amount first. You will choose Pokémon after someone joins.
                            </p>
                        </div>

                        <span class="w-fit rounded-full border border-yellow-300/20 bg-yellow-400/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-yellow-200">
                            New Battle Room
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('pokemon-lobby.store') }}" class="grid grid-cols-1 gap-4 p-6 md:grid-cols-[1fr_auto]">
                    @csrf

                    <div>
                        <label class="mb-2 block text-sm font-black text-slate-200">
                            Bet Amount
                        </label>

                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-yellow-300">
                                ₱
                            </span>

                            <input
                                name="bet_amount"
                                type="number"
                                value="{{ old('bet_amount', 10) }}"
                                min="1"
                                class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-10 py-4 text-sm font-black text-white outline-none transition placeholder:text-slate-500 focus:border-yellow-300/50 focus:ring-4 focus:ring-yellow-300/10"
                                required
                            >
                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            This amount will be used as your lobby bet.
                        </p>
                    </div>

                    <div class="flex items-end">
                        <button class="w-full rounded-2xl bg-yellow-400 px-6 py-4 text-sm font-black text-slate-950 shadow-lg shadow-yellow-950/30 transition hover:bg-yellow-300 md:w-auto">
                            Create Lobby
                        </button>
                    </div>
                </form>
            </section>

            {{-- Lobby Lists --}}
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                {{-- Available Lobbies --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-black text-white">
                                    Available Lobbies
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Join a lobby waiting for an opponent.
                                </p>
                            </div>

                            <span class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-black text-blue-200 ring-1 ring-blue-300/20">
                                {{ $waitingLobbies->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4 p-6">
                        @forelse ($waitingLobbies as $lobby)
                            <div class="group overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60 p-5 transition hover:border-blue-300/40 hover:bg-slate-900/70">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-500/15 text-2xl ring-1 ring-blue-300/20">
                                                ⚔️
                                            </div>

                                            <div>
                                                <h3 class="text-lg font-black text-white">
                                                    {{ $lobby->playerOne?->username }}'s Lobby
                                                </h3>

                                                <p class="text-xs font-semibold text-slate-400">
                                                    Lobby #{{ $lobby->id }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="rounded-full bg-yellow-400/10 px-3 py-1 text-xs font-black text-yellow-200 ring-1 ring-yellow-300/20">
                                                Bet: ₱{{ number_format($lobby->bet_amount, 2) }}
                                            </span>

                                            <span class="rounded-full bg-green-500/10 px-3 py-1 text-xs font-black text-green-200 ring-1 ring-green-300/20">
                                                Waiting
                                            </span>
                                        </div>
                                    </div>

                                    <form method="POST" action="{{ route('pokemon-lobby.join', $lobby) }}" class="sm:min-w-[150px]">
                                        @csrf

                                        <button class="w-full rounded-2xl bg-blue-600 px-4 py-3 text-sm font-black text-white transition hover:bg-blue-700">
                                            Join Lobby
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-3xl border border-white/10 bg-slate-950/60 px-5 py-12 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-500/15 text-3xl ring-1 ring-blue-300/20">
                                    🔎
                                </div>

                                <h3 class="mt-5 text-xl font-black text-white">
                                    No available lobbies
                                </h3>

                                <p class="mt-2 text-sm text-slate-400">
                                    Create a lobby and wait for another player to join.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- My Lobbies --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-black text-white">
                                    My Lobbies
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Continue your active or waiting battle rooms.
                                </p>
                            </div>

                            <span class="rounded-full bg-violet-500/10 px-3 py-1 text-xs font-black text-violet-200 ring-1 ring-violet-300/20">
                                {{ $myLobbies->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-4 p-6">
                        @forelse ($myLobbies as $lobby)
                            <a href="{{ route('pokemon-lobby.show', $lobby) }}"
                               class="group block overflow-hidden rounded-3xl border border-white/10 bg-slate-950/60 p-5 transition hover:border-yellow-300/50 hover:bg-slate-900/70">

                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-yellow-400/15 text-2xl ring-1 ring-yellow-300/20">
                                            🏟️
                                        </div>

                                        <div>
                                            <h3 class="text-lg font-black text-white">
                                                Lobby #{{ $lobby->id }}
                                            </h3>

                                            <p class="text-xs font-semibold text-slate-400">
                                                Round {{ $lobby->round_number }}
                                            </p>
                                        </div>
                                    </div>

                                    <span class="text-2xl text-slate-500 transition group-hover:translate-x-1 group-hover:text-yellow-200">
                                        →
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="rounded-full bg-yellow-400/10 px-3 py-1 text-xs font-black text-yellow-200 ring-1 ring-yellow-300/20">
                                        ₱{{ number_format($lobby->bet_amount, 2) }}
                                    </span>

                                    @if ($lobby->status === 'waiting')
                                        <span class="rounded-full bg-blue-500/10 px-3 py-1 text-xs font-black text-blue-200 ring-1 ring-blue-300/20">
                                            WAITING
                                        </span>
                                    @elseif ($lobby->status === 'active')
                                        <span class="rounded-full bg-green-500/10 px-3 py-1 text-xs font-black text-green-200 ring-1 ring-green-300/20">
                                            ACTIVE
                                        </span>
                                    @elseif ($lobby->status === 'finished')
                                        <span class="rounded-full bg-slate-500/10 px-3 py-1 text-xs font-black text-slate-200 ring-1 ring-slate-300/20">
                                            FINISHED
                                        </span>
                                    @else
                                        <span class="rounded-full bg-violet-500/10 px-3 py-1 text-xs font-black text-violet-200 ring-1 ring-violet-300/20">
                                            {{ strtoupper($lobby->status) }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="rounded-3xl border border-white/10 bg-slate-950/60 px-5 py-12 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-yellow-400/15 text-3xl ring-1 ring-yellow-300/20">
                                    🕹️
                                </div>

                                <h3 class="mt-5 text-xl font-black text-white">
                                    You have no lobbies yet
                                </h3>

                                <p class="mt-2 text-sm text-slate-400">
                                    Create or join a lobby to start battling.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </section>

        </main>

    </div>

</body>
</html>