<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#1d4ed8_0,#020617_45%,#020617_100%)]">

        {{-- Header --}}
        <nav class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/85 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-4">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    {{-- Left --}}
                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">
                            Player Dashboard
                        </h1>

                        <p class="mt-1 text-sm text-slate-400">
                            Choose your game and manage your wallet.
                        </p>
                    </div>

                    {{-- Right Nav Cards --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap lg:items-center lg:justify-end">

                        {{-- Balance --}}
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

                        {{-- Withdraw --}}
                        <a href="{{ route('withdrawals.index') }}"
                           class="group rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 shadow-lg shadow-cyan-950/20 transition hover:border-cyan-300/50 hover:bg-cyan-400/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/20 text-lg transition group-hover:bg-cyan-500/30">
                                    🏦
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wide text-cyan-300">
                                        Withdraw
                                    </p>

                                    <p class="text-sm font-bold text-white">
                                        Request
                                    </p>
                                </div>
                            </div>
                        </a>

                        {{-- Account Status --}}
                        <div class="rounded-2xl border border-blue-400/20 bg-blue-400/10 px-4 py-3 shadow-lg shadow-blue-950/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20 text-lg">
                                    ✅
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wide text-blue-300">
                                        Status
                                    </p>

                                    <p class="text-sm font-black text-emerald-300">
                                        {{ ucfirst(auth()->user()->status ?? 'active') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Player ID --}}
                        <div class="rounded-2xl border border-violet-400/20 bg-violet-400/10 px-4 py-3 shadow-lg shadow-violet-950/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-500/20 text-lg">
                                    🆔
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wide text-violet-300">
                                        Player ID
                                    </p>

                                    <p class="text-sm font-black text-white">
                                        #{{ auth()->id() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Logout --}}
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="h-full w-full rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-950/30 transition hover:bg-red-700 sm:w-auto">
                                Logout
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8">

            {{-- Welcome --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur">
                <div class="relative p-6 md:p-8">
                    <div class="absolute right-0 top-0 h-48 w-48 rounded-full bg-blue-500/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-48 w-48 rounded-full bg-emerald-500/20 blur-3xl"></div>

                    <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_330px] lg:items-center">
                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-blue-300">
                                Welcome Back
                            </p>

                            <h2 class="mt-3 text-4xl font-black text-white md:text-5xl">
                                {{ auth()->user()->username }}
                            </h2>

                            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 md:text-base">
                                Select a game below. You can enter the Totalizator arena or play Pokémon Battle against another player.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-white/10 bg-slate-950/50 p-5">
                            <p class="text-sm font-bold text-slate-400">
                                Available Balance
                            </p>

                            <p class="mt-2 text-4xl font-black text-emerald-300">
                                ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                This is your current playable wallet balance.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Games --}}
            <section>
                <div class="mb-5">
                    <h2 class="text-2xl font-black text-white">
                        Choose Your Game
                    </h2>

                    <p class="mt-1 text-sm text-slate-400">
                        Pick one game to continue.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    {{-- Totalizator --}}
                    <a href="{{ route('player.totalizator') }}"
                       class="group relative overflow-hidden rounded-3xl border border-red-400/20 bg-white/10 p-6 shadow-xl backdrop-blur transition hover:-translate-y-1 hover:border-red-400/60 hover:bg-white/[0.14]">

                        <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-red-500/20 blur-3xl transition group-hover:bg-red-500/30"></div>

                        <div class="relative">
                            <div class="mb-6 flex items-center justify-between gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-red-600 text-4xl shadow-lg shadow-red-950/60">
                                    🐓
                                </div>

                                <span class="rounded-full border border-red-300/20 bg-red-500/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-red-200">
                                    Live Betting
                                </span>
                            </div>

                            <h3 class="text-3xl font-black text-white">
                                Totalizator
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-300">
                                Enter the live arena, choose your side, place your bet, and wait for the declared result.
                            </p>

                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Type</p>
                                    <p class="mt-1 text-sm font-black text-white">Live</p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Mode</p>
                                    <p class="mt-1 text-sm font-black text-white">Betting</p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Status</p>
                                    <p class="mt-1 text-sm font-black text-emerald-300">Open</p>
                                </div>
                            </div>

                            <div class="mt-7 flex items-center justify-between border-t border-white/10 pt-5">
                                <span class="text-sm font-black text-red-200">
                                    Play Totalizator
                                </span>

                                <span class="text-2xl text-white transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Pokemon --}}
                    <a href="{{ route('pokemon-lobby.index') }}"
                       class="group relative overflow-hidden rounded-3xl border border-blue-400/20 bg-white/10 p-6 shadow-xl backdrop-blur transition hover:-translate-y-1 hover:border-blue-400/60 hover:bg-white/[0.14]">

                        <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-blue-500/20 blur-3xl transition group-hover:bg-blue-500/30"></div>

                        <div class="relative">
                            <div class="mb-6 flex items-center justify-between gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-blue-600 text-4xl shadow-lg shadow-blue-950/60">
                                    ⚡
                                </div>

                                <span class="rounded-full border border-blue-300/20 bg-blue-500/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-blue-200">
                                    PvP Battle
                                </span>
                            </div>

                            <h3 class="text-3xl font-black text-white">
                                Pokémon Battle
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-300">
                                Create or join a battle room, choose your Pokémon, and fight against another player.
                            </p>

                            <div class="mt-6 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Type</p>
                                    <p class="mt-1 text-sm font-black text-white">PvP</p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Mode</p>
                                    <p class="mt-1 text-sm font-black text-white">Battle</p>
                                </div>

                                <div class="rounded-2xl border border-white/10 bg-slate-950/40 p-3 text-center">
                                    <p class="text-xs text-slate-400">Status</p>
                                    <p class="mt-1 text-sm font-black text-emerald-300">Open</p>
                                </div>
                            </div>

                            <div class="mt-7 flex items-center justify-between border-t border-white/10 pt-5">
                                <span class="text-sm font-black text-blue-200">
                                    Play Pokémon
                                </span>

                                <span class="text-2xl text-white transition group-hover:translate-x-1">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                </div>
            </section>

        </main>
    </div>

</body>
</html>