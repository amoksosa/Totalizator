<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pokémon Battle Room</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .type-filter-btn {
            border-radius: 9999px;
            background: #1e293b;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 800;
            color: #e2e8f0;
            border: 1px solid #334155;
            transition: 0.2s ease;
        }

        .type-filter-btn:hover {
            background: #334155;
            border-color: #eab308;
        }

        .type-filter-btn.active {
            background: #eab308;
            color: #000;
            border-color: #eab308;
        }

        .pokemon-option-card {
            border-radius: 18px;
            background: #020617;
            border: 1px solid #1e293b;
            padding: 12px;
            text-align: center;
            transition: 0.2s ease;
        }

        .pokemon-option-card:hover {
            border-color: #eab308;
            transform: translateY(-2px);
        }

        .pokemon-option-card.active {
            border-color: #22c55e;
            box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.45);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-white">

@php
    $isPlayerOne = $lobby->player_one_id === auth()->id();
    $isPlayerTwo = $lobby->player_two_id === auth()->id();

    $myPokemon = $isPlayerOne ? $lobby->player_one_pokemon : $lobby->player_two_pokemon;

    $myReady = $isPlayerOne ? $lobby->player_one_ready : $lobby->player_two_ready;
    $enemyReady = $isPlayerOne ? $lobby->player_two_ready : $lobby->player_one_ready;

    $p1 = $lobby->player_one_data;
    $p2 = $lobby->player_two_data;

    $roundFinished = $lobby->status === 'active' && $lobby->finished_at;
    $isDraw = $roundFinished && ! $lobby->winner_id;
    $iWon = $roundFinished && $lobby->winner_id === auth()->id();

    $bothPicked = $lobby->player_one_pokemon && $lobby->player_two_pokemon;

    $remainingPickSeconds = $lobby->choice_deadline
        ? max(0, now()->diffInSeconds($lobby->choice_deadline, false))
        : 0;

    $pickTimeEnded = $lobby->choice_deadline && $remainingPickSeconds <= 0;

    $shouldRevealPokemon = $roundFinished || $bothPicked || $pickTimeEnded;

    $canReady = $shouldRevealPokemon && $myPokemon && ! $roundFinished;

    $myReadyAfterRound = $isPlayerOne ? $lobby->player_one_ready : $lobby->player_two_ready;
    $enemyReadyAfterRound = $isPlayerOne ? $lobby->player_two_ready : $lobby->player_one_ready;
@endphp

    <nav class="bg-slate-900 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">Pokémon Battle Room #{{ $lobby->id }}</h1>
                <p class="text-sm text-slate-400">
                    Round {{ $lobby->round_number }} • Status: {{ strtoupper($lobby->status) }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold">
                    Balance: ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                </div>

                <a href="{{ route('pokemon-lobby.index') }}"
                   class="rounded-xl bg-slate-700 hover:bg-slate-600 px-4 py-2 text-sm font-bold">
                    Lobby List
                </a>

                <form method="POST" action="{{ route('pokemon-lobby.leave', $lobby) }}">
                    @csrf
                    @method('PATCH')

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-bold">
                        Leave Lobby
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        @if (session('success'))
            <div class="mb-6 rounded-xl bg-green-900/50 border border-green-700 text-green-200 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-900/50 border border-red-700 text-red-200 px-4 py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="rounded-3xl bg-slate-900 border border-slate-800 p-6">
            <div class="grid md:grid-cols-4 gap-4 text-center">
                <div class="rounded-xl bg-slate-950 p-4">
                    <p class="text-slate-400 text-sm">Player 1</p>
                    <p class="text-xl font-black">{{ $lobby->playerOne?->username }}</p>
                    <p class="text-blue-300 font-bold">Score: {{ $lobby->player_one_score }}</p>
                    <p class="mt-1 text-xs font-bold {{ $lobby->player_one_ready ? 'text-green-300' : 'text-yellow-300' }}">
                        {{ $lobby->player_one_ready ? 'READY' : 'NOT READY' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-950 p-4">
                    <p class="text-slate-400 text-sm">Player 2</p>
                    <p class="text-xl font-black">{{ $lobby->playerTwo?->username ?? 'Waiting...' }}</p>
                    <p class="text-red-300 font-bold">Score: {{ $lobby->player_two_score }}</p>
                    <p class="mt-1 text-xs font-bold {{ $lobby->player_two_ready ? 'text-green-300' : 'text-yellow-300' }}">
                        {{ $lobby->player_two_ready ? 'READY' : 'NOT READY' }}
                    </p>
                </div>

                <div class="rounded-xl bg-slate-950 p-4">
                    <p class="text-slate-400 text-sm">Bet</p>
                    <p class="text-xl font-black text-yellow-300">₱{{ number_format($lobby->bet_amount, 2) }}</p>
                </div>

                <div class="rounded-xl bg-slate-950 p-4">
                    <p class="text-slate-400 text-sm">Pot</p>
                    <p class="text-xl font-black text-green-300">₱{{ number_format($lobby->pot_amount, 2) }}</p>
                </div>
            </div>
        </section>

        @if ($lobby->status === 'waiting')
            <section class="mt-6 rounded-3xl bg-blue-900/40 border border-blue-700 p-6">
                <h2 class="text-2xl font-black">Waiting for opponent...</h2>
                <p class="text-blue-100 mt-2">
                    Share or wait until another player joins this lobby.
                </p>
            </section>
        @endif

        @if ($lobby->status === 'active' && ! $roundFinished)
            <section class="mt-6 rounded-3xl bg-blue-900/40 border border-blue-700 p-6">
                <h2 class="text-2xl font-black">Choose your Pokémon</h2>
                <p class="mt-2 text-blue-100">
                    Pick your Pokémon. Both Pokémon will be revealed at the same time after both players pick, or after the 15-second timer ends.
                </p>

                @if (! $shouldRevealPokemon && $lobby->choice_deadline)
                    <div class="mt-4 rounded-xl bg-yellow-900/40 border border-yellow-700 p-4 text-yellow-100">
                        <p class="font-black">
                            Picking phase:
                            <span id="pickCountdown" data-seconds="{{ $remainingPickSeconds }}">
                                {{ $remainingPickSeconds }}
                            </span>
                            seconds left
                        </p>

                        <p class="text-sm mt-1 text-yellow-200">
                            Pokémon are hidden until reveal time.
                        </p>
                    </div>
                @endif

                @if (! $myPokemon)
                    <form method="POST" action="{{ route('pokemon-lobby.choose', $lobby) }}" class="mt-5">
                        @csrf

                        <input type="hidden" name="pokemon" id="selectedPokemon" required>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="loadPokemonByType('all', this)" class="type-filter-btn active">All</button>
                            <button type="button" onclick="loadPokemonByType('fire', this)" class="type-filter-btn">Fire</button>
                            <button type="button" onclick="loadPokemonByType('water', this)" class="type-filter-btn">Water</button>
                            <button type="button" onclick="loadPokemonByType('electric', this)" class="type-filter-btn">Electric</button>
                            <button type="button" onclick="loadPokemonByType('ground', this)" class="type-filter-btn">Earth</button>
                            <button type="button" onclick="loadPokemonByType('grass', this)" class="type-filter-btn">Grass</button>
                            <button type="button" onclick="loadPokemonByType('psychic', this)" class="type-filter-btn">Psychic</button>
                            <button type="button" onclick="loadPokemonByType('dragon', this)" class="type-filter-btn">Dragon</button>
                            <button type="button" onclick="loadPokemonByType('fighting', this)" class="type-filter-btn">Fighting</button>
                            <button type="button" onclick="loadPokemonByType('ice', this)" class="type-filter-btn">Ice</button>
                            <button type="button" onclick="loadPokemonByType('rock', this)" class="type-filter-btn">Rock</button>
                            <button type="button" onclick="loadPokemonByType('ghost', this)" class="type-filter-btn">Ghost</button>
                            <button type="button" onclick="loadPokemonByType('poison', this)" class="type-filter-btn">Poison</button>
                            <button type="button" onclick="loadPokemonByType('flying', this)" class="type-filter-btn">Flying</button>
                            <button type="button" onclick="loadPokemonByType('bug', this)" class="type-filter-btn">Bug</button>
                        </div>

                        <div class="mt-5 rounded-2xl bg-slate-950 border border-slate-800 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-slate-400">Selected Pokémon</p>
                                    <p id="selectedPokemonName" class="text-xl font-black text-yellow-300">
                                        None selected
                                    </p>
                                </div>

                                <button class="rounded-xl bg-yellow-500 hover:bg-yellow-600 text-black px-6 py-3 font-black">
                                    Choose Pokémon
                                </button>
                            </div>
                        </div>

                        <div id="pokemonGrid" class="mt-5 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <div class="col-span-full text-slate-400">
                                Loading Pokémon...
                            </div>
                        </div>
                    </form>
                @else
                    <div class="mt-4 rounded-xl bg-green-900/40 border border-green-700 p-4 text-green-200">
                        You locked in your Pokémon.
                        @if ($shouldRevealPokemon)
                            <span class="font-black">
                                You chose: {{ ucfirst(str_replace('-', ' ', $myPokemon)) }}
                            </span>
                        @else
                            <span class="font-black">
                                Waiting for reveal...
                            </span>
                        @endif
                    </div>

                    <div class="mt-5 grid md:grid-cols-2 gap-4">
                        <div class="rounded-xl bg-slate-950 border border-slate-800 p-4">
                            <p class="text-sm text-slate-400">Your Status</p>
                            <p class="text-xl font-black {{ $myReady ? 'text-green-300' : 'text-yellow-300' }}">
                                {{ $myReady ? 'READY' : 'NOT READY' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-950 border border-slate-800 p-4">
                            <p class="text-sm text-slate-400">Opponent Status</p>
                            <p class="text-xl font-black {{ $enemyReady ? 'text-green-300' : 'text-yellow-300' }}">
                                {{ $enemyReady ? 'READY' : 'NOT READY' }}
                            </p>
                        </div>
                    </div>

                    @if (! $canReady)
                        <div class="mt-5 rounded-xl bg-yellow-900/40 border border-yellow-700 p-4 text-yellow-200">
                            Waiting for Pokémon reveal before Ready is available...
                        </div>
                    @elseif (! $myReady)
                        <form method="POST" action="{{ route('pokemon-lobby.ready', $lobby) }}" class="mt-5">
                            @csrf

                            <button class="w-full rounded-xl bg-green-600 hover:bg-green-700 text-white px-6 py-3 font-black">
                                Ready
                            </button>
                        </form>
                    @else
                        <div class="mt-5 rounded-xl bg-green-900/40 border border-green-700 p-4 text-green-200">
                            Waiting for opponent to press Ready...
                        </div>
                    @endif
                @endif
            </section>
        @endif

        <section class="mt-8 grid lg:grid-cols-2 gap-6">
            <div class="rounded-3xl bg-slate-900 border border-blue-800 p-6 text-center">
                <h2 class="text-xl font-black text-blue-300">
                    {{ $lobby->playerOne?->username }}
                </h2>

                @if ($p1 && $shouldRevealPokemon)
                    <img src="{{ $p1['image'] ?? '' }}" class="mx-auto h-56 object-contain">
                    <h3 class="text-3xl font-black">{{ $p1['name'] ?? '' }}</h3>
                    <p class="text-slate-400">Power: {{ $lobby->player_one_power }}</p>
                @elseif ($p1 && ! $shouldRevealPokemon)
                    <div class="h-56 flex flex-col items-center justify-center text-yellow-300">
                        <div class="text-6xl">?</div>
                        <p class="font-black mt-2">Pokémon Locked In</p>
                        <p class="text-sm text-slate-400">Waiting for reveal...</p>
                    </div>
                @else
                    <div class="h-56 flex items-center justify-center text-slate-500">
                        No Pokémon chosen yet
                    </div>
                @endif
            </div>

            <div class="rounded-3xl bg-slate-900 border border-red-800 p-6 text-center">
                <h2 class="text-xl font-black text-red-300">
                    {{ $lobby->playerTwo?->username ?? 'Waiting for opponent' }}
                </h2>

                @if ($p2 && $shouldRevealPokemon)
                    <img src="{{ $p2['image'] ?? '' }}" class="mx-auto h-56 object-contain">
                    <h3 class="text-3xl font-black">{{ $p2['name'] ?? '' }}</h3>
                    <p class="text-slate-400">Power: {{ $lobby->player_two_power }}</p>
                @elseif ($p2 && ! $shouldRevealPokemon)
                    <div class="h-56 flex flex-col items-center justify-center text-yellow-300">
                        <div class="text-6xl">?</div>
                        <p class="font-black mt-2">Pokémon Locked In</p>
                        <p class="text-sm text-slate-400">Waiting for reveal...</p>
                    </div>
                @else
                    <div class="h-56 flex items-center justify-center text-slate-500">
                        No Pokémon chosen yet
                    </div>
                @endif
            </div>
        </section>

        @if ($roundFinished)
            <section class="mt-8 rounded-3xl p-6
                {{ $isDraw ? 'bg-yellow-900/40 border border-yellow-700' : ($iWon ? 'bg-green-900/40 border border-green-700' : 'bg-red-900/40 border border-red-700') }}">
                @if ($isDraw)
                    <h2 class="text-3xl font-black text-yellow-200">Draw!</h2>
                    <p class="mt-2 text-yellow-100">Both players were refunded.</p>
                @elseif ($iWon)
                    <h2 class="text-3xl font-black text-green-200">You won!</h2>

                    <p class="mt-1 text-green-100 font-black">
                        You received: ₱{{ number_format($lobby->payout_amount, 2) }}
                    </p>
                @else
                    <h2 class="text-3xl font-black text-red-200">You lost!</h2>
                    <p class="mt-2 text-red-100">Winner: {{ $lobby->winner?->username }}</p>
                @endif

                <div class="mt-6 grid md:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-slate-950 border border-slate-800 p-4">
                        <p class="text-sm text-slate-400">Your Next Fight Status</p>
                        <p class="text-xl font-black {{ $myReadyAfterRound ? 'text-green-300' : 'text-yellow-300' }}">
                            {{ $myReadyAfterRound ? 'READY' : 'NOT READY' }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-slate-950 border border-slate-800 p-4">
                        <p class="text-sm text-slate-400">Opponent Next Fight Status</p>
                        <p class="text-xl font-black {{ $enemyReadyAfterRound ? 'text-green-300' : 'text-yellow-300' }}">
                            {{ $enemyReadyAfterRound ? 'READY' : 'NOT READY' }}
                        </p>
                    </div>
                </div>

                @if (! $myReadyAfterRound)
                    <form method="POST" action="{{ route('pokemon-lobby.ready', $lobby) }}" class="mt-5">
                        @csrf

                        <button class="w-full rounded-xl bg-green-600 hover:bg-green-700 text-white px-6 py-3 font-black">
                            Ready for Next Fight
                        </button>
                    </form>
                @else
                    <div class="mt-5 rounded-xl bg-green-900/40 border border-green-700 p-4 text-green-200">
                        Waiting for opponent to press Ready for the next fight...
                    </div>
                @endif

                <p class="mt-4 text-sm text-slate-200">
                    This lobby will remain until someone clicks Leave Lobby.
                </p>
            </section>
        @endif

        @if ($lobby->status === 'closed')
            <section class="mt-8 rounded-3xl bg-slate-800 border border-slate-700 p-6">
                <h2 class="text-2xl font-black">Lobby Closed</h2>
                <p class="text-slate-300 mt-2">
                    Someone left the lobby.
                </p>
            </section>
        @endif

    </main>

    <script>
        const pokemonOptionsUrl = "{{ route('pokemon-lobby.pokemon-options') }}";

        async function loadPokemonByType(type = 'all', clickedButton = null) {
            const grid = document.getElementById('pokemonGrid');

            if (!grid) {
                return;
            }

            document.querySelectorAll('.type-filter-btn').forEach(function (btn) {
                btn.classList.remove('active');
            });

            if (clickedButton) {
                clickedButton.classList.add('active');
            }

            grid.innerHTML = `
                <div class="col-span-full text-slate-400">
                    Loading Pokémon...
                </div>
            `;

            try {
                const response = await fetch(`${pokemonOptionsUrl}?type=${type}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (!data.success) {
                    grid.innerHTML = `
                        <div class="col-span-full text-red-300">
                            ${data.message || 'Unable to load Pokémon.'}
                        </div>
                    `;
                    return;
                }

                if (!data.pokemon || data.pokemon.length === 0) {
                    grid.innerHTML = `
                        <div class="col-span-full text-slate-400">
                            No Pokémon found for this type.
                        </div>
                    `;
                    return;
                }

                grid.innerHTML = data.pokemon.map(function (pokemon) {
                    return `
                        <button
                            type="button"
                            class="pokemon-option-card"
                            data-pokemon-name="${pokemon.name}"
                            onclick="selectPokemonCard(this, '${pokemon.name}', '${pokemon.display_name}')"
                        >
                            <img
                                src="${pokemon.image}"
                                alt="${pokemon.display_name}"
                                class="mx-auto h-24 w-24 object-contain"
                                loading="lazy"
                            >

                            <div class="mt-2 text-sm font-black text-white">
                                ${pokemon.display_name}
                            </div>

                            <div class="text-xs text-slate-500">
                                #${pokemon.id}
                            </div>
                        </button>
                    `;
                }).join('');
            } catch (error) {
                grid.innerHTML = `
                    <div class="col-span-full text-red-300">
                        Unable to connect to PokéAPI. Check your internet connection.
                    </div>
                `;
            }
        }

        function selectPokemonCard(button, pokemonName, displayName) {
            document.querySelectorAll('.pokemon-option-card').forEach(function (card) {
                card.classList.remove('active');
            });

            button.classList.add('active');

            document.getElementById('selectedPokemon').value = pokemonName;
            document.getElementById('selectedPokemonName').innerText = displayName;
        }

        document.addEventListener('DOMContentLoaded', function () {
            if (document.getElementById('pokemonGrid')) {
                loadPokemonByType('all', document.querySelector('.type-filter-btn.active'));
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const countdown = document.getElementById('pickCountdown');

            if (!countdown) {
                return;
            }

            let seconds = parseInt(countdown.dataset.seconds || '0', 10);

            const timer = setInterval(function () {
                seconds--;

                if (seconds <= 0) {
                    clearInterval(timer);
                    countdown.textContent = '0';
                    window.location.reload();
                    return;
                }

                countdown.textContent = seconds;
            }, 1000);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Echo) {
                console.error('Laravel Echo is not loaded.');
                return;
            }

            window.Echo
                .channel('pokemon-lobby.{{ $lobby->id }}')
                .listen('.pokemon-lobby.updated', function (event) {
                    console.log('Pokemon lobby updated:', event);
                    window.location.reload();
                });
        });
    </script>
</body>
</html>