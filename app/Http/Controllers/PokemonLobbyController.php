<?php

namespace App\Http\Controllers;

use App\Events\PokemonLobbyUpdated;
use App\Models\AgentCommission;
use App\Models\GameSalesReport;
use App\Models\PokemonLobby;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PokemonLobbyController extends Controller
{
    public function index()
    {
        $this->guardPlayer();

        $waitingLobbies = PokemonLobby::with(['playerOne'])
            ->where('status', 'waiting')
            ->where('player_one_id', '!=', auth()->id())
            ->latest()
            ->get();

        $myLobbies = PokemonLobby::with(['playerOne', 'playerTwo', 'winner'])
            ->where(function ($query) {
                $query->where('player_one_id', auth()->id())
                    ->orWhere('player_two_id', auth()->id());
            })
            ->latest()
            ->take(20)
            ->get();

        return view('player.pokemon-lobby', compact('waitingLobbies', 'myLobbies'));
    }

    public function show(PokemonLobby $lobby)
    {
        $this->guardPlayer();

        if (! $this->isParticipant($lobby)) {
            abort(403);
        }

        if (
            $lobby->status === 'active' &&
            ! $lobby->finished_at &&
            ! $lobby->choice_deadline &&
            $lobby->player_two_id
        ) {
            $lobby->choice_deadline = now()->addSeconds(15);
            $lobby->save();

            $this->broadcastLobby($lobby->id);
        }

        $lobby->refresh();
        $lobby->load(['playerOne', 'playerTwo', 'winner']);

        return view('player.pokemon-game', [
            'lobby' => $lobby,
            'pokemonNames' => $this->pokemonNames(),
        ]);
    }

    public function state(PokemonLobby $lobby)
    {
        $this->guardPlayer();

        if (! $this->isParticipant($lobby)) {
            abort(403);
        }

        $lobby->refresh();

        return response()->json($this->lobbyPayload($lobby));
    }

    public function store(Request $request)
    {
        $this->guardPlayer();

        $validated = $request->validate([
            'bet_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $betAmount = round((float) $validated['bet_amount'], 2);

        try {
            $lobby = DB::transaction(function () use ($betAmount) {
                $player = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $player) {
                    throw new \RuntimeException('Player not found.');
                }

                if ((float) $player->credit_balance < $betAmount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                $player->decrement('credit_balance', $betAmount);

                return PokemonLobby::create([
                    'player_one_id' => $player->id,
                    'player_two_id' => null,

                    'player_one_pokemon' => null,
                    'player_two_pokemon' => null,

                    'player_one_power' => 0,
                    'player_two_power' => 0,

                    'player_one_data' => null,
                    'player_two_data' => null,

                    'bet_amount' => $betAmount,
                    'pot_amount' => $betAmount,
                    'payout_amount' => 0,

                    'gross_payout_amount' => 0,
                    'commission_amount' => 0,
                    'agent_commission_amount' => 0,
                    'company_commission_amount' => 0,
                    'agent_id' => null,

                    'winner_id' => null,
                    'status' => 'waiting',

                    'choice_deadline' => null,
                    'finished_at' => null,
                    'closed_at' => null,

                    'round_number' => 1,
                    'player_one_score' => 0,
                    'player_two_score' => 0,

                    'player_one_ready' => false,
                    'player_two_ready' => false,
                ]);
            });

            $this->broadcastLobby($lobby->id);

            return redirect()
                ->route('pokemon-lobby.show', $lobby)
                ->with('success', 'Lobby created. Waiting for opponent.');
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['bet_amount' => $e->getMessage()])
                ->withInput();
        } catch (\Throwable $e) {
            Log::error('Create Pokémon lobby failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()
                ->withErrors(['bet_amount' => 'Something went wrong while creating lobby.'])
                ->withInput();
        }
    }

    public function join(PokemonLobby $lobby)
    {
        $this->guardPlayer();

        try {
            DB::transaction(function () use ($lobby) {
                $lobby = PokemonLobby::where('id', $lobby->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lobby) {
                    throw new \RuntimeException('Lobby not found.');
                }

                if ($lobby->status !== 'waiting') {
                    throw new \RuntimeException('This lobby is no longer available.');
                }

                if ((int) $lobby->player_one_id === (int) auth()->id()) {
                    throw new \RuntimeException('You cannot join your own lobby.');
                }

                $playerTwo = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $playerTwo) {
                    throw new \RuntimeException('Player not found.');
                }

                $betAmount = round((float) $lobby->bet_amount, 2);

                if ((float) $playerTwo->credit_balance < $betAmount) {
                    throw new \RuntimeException('Insufficient credit balance.');
                }

                $playerTwo->decrement('credit_balance', $betAmount);

                $lobby->update([
                    'player_two_id' => $playerTwo->id,
                    'pot_amount' => round($betAmount * 2, 2),
                    'status' => 'active',
                    'choice_deadline' => now()->addSeconds(15),
                    'finished_at' => null,
                    'player_one_ready' => false,
                    'player_two_ready' => false,
                ]);
            });

            $this->broadcastLobby($lobby->id);

            return redirect()
                ->route('pokemon-lobby.show', $lobby)
                ->with('success', 'You joined the lobby. Choose your Pokémon.');
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'pokemon' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Join Pokémon lobby failed', [
                'message' => $e->getMessage(),
                'lobby_id' => $lobby->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'pokemon' => 'Something went wrong while joining lobby.',
            ]);
        }
    }

    public function choose(Request $request, PokemonLobby $lobby)
    {
        $this->guardPlayer();

        $validated = $request->validate([
            'pokemon' => ['required', 'string', 'max:50'],
        ]);

        if (! $this->isParticipant($lobby)) {
            abort(403);
        }

        if ($lobby->status !== 'active') {
            return back()->withErrors([
                'pokemon' => 'This lobby is not accepting Pokémon choices.',
            ]);
        }

        if ($lobby->finished_at) {
            return back()->withErrors([
                'pokemon' => 'This battle is already finished. Press Ready for the next fight.',
            ]);
        }

        $pokemonName = strtolower(trim($validated['pokemon']));

        try {
            $pokemon = $this->fetchPokemon($pokemonName);

            if (! $pokemon) {
                return back()
                    ->withErrors(['pokemon' => 'Pokémon not found.'])
                    ->withInput();
            }

            $pokemonPower = $this->calculatePower($pokemon);
            $formattedPokemon = $this->formatPokemon($pokemon, $pokemonPower);

            DB::transaction(function () use ($lobby, $pokemonName, $pokemonPower, $formattedPokemon) {
                $lobby = PokemonLobby::where('id', $lobby->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lobby || $lobby->status !== 'active') {
                    throw new \RuntimeException('This lobby is not active.');
                }

                if ($lobby->finished_at) {
                    throw new \RuntimeException('This battle is already finished. Press Ready for the next fight.');
                }

                if (! $lobby->choice_deadline) {
                    $lobby->choice_deadline = now()->addSeconds(15);
                    $lobby->save();
                }

                if ((int) $lobby->player_one_id === (int) auth()->id()) {
                    $lobby->update([
                        'player_one_pokemon' => $pokemonName,
                        'player_one_power' => $pokemonPower,
                        'player_one_data' => $formattedPokemon,
                        'player_one_ready' => false,
                    ]);
                } elseif ((int) $lobby->player_two_id === (int) auth()->id()) {
                    $lobby->update([
                        'player_two_pokemon' => $pokemonName,
                        'player_two_power' => $pokemonPower,
                        'player_two_data' => $formattedPokemon,
                        'player_two_ready' => false,
                    ]);
                } else {
                    throw new \RuntimeException('You are not part of this lobby.');
                }
            });

            $this->broadcastLobby($lobby->id);

            return redirect()
                ->route('pokemon-lobby.show', $lobby)
                ->with('success', 'Pokémon locked in. Wait for reveal.');
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'pokemon' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Choose Pokémon failed', [
                'message' => $e->getMessage(),
                'lobby_id' => $lobby->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'pokemon' => 'Something went wrong while choosing Pokémon.',
            ]);
        }
    }

    public function ready(PokemonLobby $lobby)
    {
        $this->guardPlayer();

        if (! $this->isParticipant($lobby)) {
            abort(403);
        }

        try {
            $message = 'Ready confirmed.';

            DB::transaction(function () use ($lobby, &$message) {
                $lobby = PokemonLobby::where('id', $lobby->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lobby) {
                    throw new \RuntimeException('Lobby not found.');
                }

                if ($lobby->status !== 'active') {
                    throw new \RuntimeException('This lobby is not active.');
                }

                if (! $lobby->player_two_id) {
                    throw new \RuntimeException('Waiting for opponent.');
                }

                if ($lobby->finished_at) {
                    if ((int) $lobby->player_one_id === (int) auth()->id()) {
                        $lobby->player_one_ready = true;
                    } elseif ((int) $lobby->player_two_id === (int) auth()->id()) {
                        $lobby->player_two_ready = true;
                    } else {
                        throw new \RuntimeException('You are not part of this lobby.');
                    }

                    $lobby->save();

                    if ($lobby->player_one_ready && $lobby->player_two_ready) {
                        $this->startNextReadyRound($lobby);
                        $message = 'Both players are ready. Next round started.';
                    }

                    return;
                }

                if (! $lobby->player_one_pokemon || ! $lobby->player_two_pokemon) {
                    throw new \RuntimeException('Both players must choose Pokémon first.');
                }

                if ((int) $lobby->player_one_id === (int) auth()->id()) {
                    $lobby->player_one_ready = true;
                } elseif ((int) $lobby->player_two_id === (int) auth()->id()) {
                    $lobby->player_two_ready = true;
                } else {
                    throw new \RuntimeException('You are not part of this lobby.');
                }

                $lobby->save();

                if ($lobby->player_one_ready && $lobby->player_two_ready) {
                    $this->resolveReadyBattle($lobby);
                    $message = 'Both players are ready. Battle finished.';
                }
            });

            $this->broadcastLobby($lobby->id);

            return redirect()
                ->route('pokemon-lobby.show', $lobby)
                ->with('success', $message);
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'pokemon' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Ready Pokémon lobby failed', [
                'message' => $e->getMessage(),
                'lobby_id' => $lobby->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'pokemon' => 'Something went wrong while setting ready.',
            ]);
        }
    }

    public function finalize(PokemonLobby $lobby)
    {
        return $this->ready($lobby);
    }

    public function nextRound(PokemonLobby $lobby)
    {
        return $this->ready($lobby);
    }

    private function resolveReadyBattle(PokemonLobby $lobby): void
    {
        $playerOne = User::where('id', $lobby->player_one_id)
            ->lockForUpdate()
            ->first();

        $playerTwo = User::where('id', $lobby->player_two_id)
            ->lockForUpdate()
            ->first();

        if (! $playerOne || ! $playerTwo) {
            throw new \RuntimeException('Players not found.');
        }

        if (! $lobby->player_one_pokemon || ! $lobby->player_two_pokemon) {
            throw new \RuntimeException('Both players must choose Pokémon first.');
        }

        $winner = null;
        $winnerId = null;

        $grossPayout = round((float) $lobby->pot_amount, 2);
        $betAmount = round((float) $lobby->bet_amount, 2);

        if ((int) $lobby->player_one_power > (int) $lobby->player_two_power) {
            $winner = $playerOne;
            $winnerId = $playerOne->id;
            $lobby->player_one_score = (int) $lobby->player_one_score + 1;
        } elseif ((int) $lobby->player_two_power > (int) $lobby->player_one_power) {
            $winner = $playerTwo;
            $winnerId = $playerTwo->id;
            $lobby->player_two_score = (int) $lobby->player_two_score + 1;
        }

        if (! $winner) {
            $playerOne->increment('credit_balance', $betAmount);
            $playerTwo->increment('credit_balance', $betAmount);

            $lobby->winner_id = null;
            $lobby->gross_payout_amount = 0;
            $lobby->commission_amount = 0;
            $lobby->agent_commission_amount = 0;
            $lobby->company_commission_amount = 0;
            $lobby->agent_id = null;
            $lobby->payout_amount = 0;
            $lobby->choice_deadline = null;
            $lobby->finished_at = now();
            $lobby->player_one_ready = false;
            $lobby->player_two_ready = false;
            $lobby->save();

            GameSalesReport::create([
                'source_game' => 'pokemon',
                'source_id' => $lobby->id,
                'event_name' => 'Pokemon Battle Room',
                'round_label' => 'Round ' . $lobby->round_number,
                'winner_id' => null,
                'agent_id' => null,
                'total_bet_amount' => round((float) $lobby->pot_amount, 2),
                'gross_payout_amount' => 0,
                'net_payout_amount' => 0,
                'commission_amount' => 0,
                'agent_commission_amount' => 0,
                'company_commission_amount' => 0,
                'status' => 'draw',
                'settled_at' => now(),
            ]);

            return;
        }

        $commissionAmount = round($grossPayout * 0.05, 2);
        $agentCommissionAmount = round($grossPayout * 0.03, 2);
        $companyCommissionAmount = round($grossPayout * 0.02, 2);
        $netPayout = round($grossPayout - $commissionAmount, 2);

        $winner->increment('credit_balance', $netPayout);

        $agentId = ! empty($winner->agent_id) ? (int) $winner->agent_id : null;

        $lobby->winner_id = $winnerId;
        $lobby->gross_payout_amount = $grossPayout;
        $lobby->commission_amount = $commissionAmount;
        $lobby->agent_commission_amount = $agentCommissionAmount;
        $lobby->company_commission_amount = $companyCommissionAmount;
        $lobby->agent_id = $agentId;
        $lobby->payout_amount = $netPayout;
        $lobby->choice_deadline = null;
        $lobby->finished_at = now();
        $lobby->player_one_ready = false;
        $lobby->player_two_ready = false;
        $lobby->save();

        $this->createPokemonAgentCommissions($lobby, $playerOne, $playerTwo);

        GameSalesReport::create([
            'source_game' => 'pokemon',
            'source_id' => $lobby->id,
            'event_name' => 'Pokemon Battle Room',
            'round_label' => 'Round ' . $lobby->round_number,
            'winner_id' => $winnerId,
            'agent_id' => $agentId,
            'total_bet_amount' => round((float) $lobby->pot_amount, 2),
            'gross_payout_amount' => $grossPayout,
            'net_payout_amount' => $netPayout,
            'commission_amount' => $commissionAmount,
            'agent_commission_amount' => $agentCommissionAmount,
            'company_commission_amount' => $companyCommissionAmount,
            'status' => 'settled',
            'settled_at' => now(),
        ]);
    }

    private function createPokemonAgentCommissions(PokemonLobby $lobby, User $playerOne, User $playerTwo): void
    {
        $betAmount = round((float) $lobby->bet_amount, 2);
        $roundLabel = 'Pokemon Lobby #' . $lobby->id . ' Round ' . $lobby->round_number;

        foreach ([$playerOne, $playerTwo] as $player) {
            if (! $player || ! $player->agent_id) {
                continue;
            }

            $commissionAmount = round($betAmount * 0.03, 2);

            $alreadyExists = AgentCommission::query()
                ->where('agent_id', $player->agent_id)
                ->where('player_id', $player->id)
                ->whereNull('bet_id')
                ->where('side', 'POKEMON')
                ->where('odds', $roundLabel)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            AgentCommission::create([
                'agent_id' => $player->agent_id,
                'player_id' => $player->id,
                'bet_id' => null,
                'bet_amount' => $betAmount,
                'commission_rate' => 3.00,
                'commission_amount' => $commissionAmount,
                'company_commission_rate' => 0,
                'company_commission_amount' => 0,
                'total_commission_rate' => 3.00,
                'total_commission_amount' => $commissionAmount,
                'conversion_status' => 'pending',
                'converted_amount' => 0,
                'side' => 'POKEMON',
                'odds' => $roundLabel,
            ]);

            $this->creditAgentCommission((int) $player->agent_id, $commissionAmount);
        }
    }

    private function creditAgentCommission(int $agentId, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $agent = User::where('id', $agentId)
            ->lockForUpdate()
            ->first();

        if (! $agent) {
            return;
        }

        if (Schema::hasColumn('users', 'commission_balance')) {
            $agent->increment('commission_balance', $amount);
            return;
        }

        $agent->increment('credit_balance', $amount);
    }

    private function startNextReadyRound(PokemonLobby $lobby): void
    {
        $playerOne = User::where('id', $lobby->player_one_id)
            ->lockForUpdate()
            ->first();

        $playerTwo = User::where('id', $lobby->player_two_id)
            ->lockForUpdate()
            ->first();

        if (! $playerOne || ! $playerTwo) {
            throw new \RuntimeException('Players not found.');
        }

        $betAmount = round((float) $lobby->bet_amount, 2);

        if ((float) $playerOne->credit_balance < $betAmount) {
            throw new \RuntimeException($playerOne->username . ' has insufficient balance.');
        }

        if ((float) $playerTwo->credit_balance < $betAmount) {
            throw new \RuntimeException($playerTwo->username . ' has insufficient balance.');
        }

        $playerOne->decrement('credit_balance', $betAmount);
        $playerTwo->decrement('credit_balance', $betAmount);

        $lobby->round_number = (int) $lobby->round_number + 1;
        $lobby->player_one_pokemon = null;
        $lobby->player_two_pokemon = null;
        $lobby->player_one_power = 0;
        $lobby->player_two_power = 0;
        $lobby->player_one_data = null;
        $lobby->player_two_data = null;
        $lobby->pot_amount = round($betAmount * 2, 2);
        $lobby->payout_amount = 0;
        $lobby->gross_payout_amount = 0;
        $lobby->commission_amount = 0;
        $lobby->agent_commission_amount = 0;
        $lobby->company_commission_amount = 0;
        $lobby->agent_id = null;
        $lobby->winner_id = null;
        $lobby->finished_at = null;
        $lobby->choice_deadline = now()->addSeconds(15);
        $lobby->player_one_ready = false;
        $lobby->player_two_ready = false;
        $lobby->save();
    }

    public function leave(PokemonLobby $lobby)
    {
        $this->guardPlayer();

        if (! $this->isParticipant($lobby)) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($lobby) {
                $lobby = PokemonLobby::where('id', $lobby->id)
                    ->lockForUpdate()
                    ->first();

                if (! $lobby) {
                    throw new \RuntimeException('Lobby not found.');
                }

                if ($lobby->status === 'closed') {
                    return;
                }

                if ($lobby->status === 'waiting' && (int) $lobby->player_one_id === (int) auth()->id()) {
                    $playerOne = User::where('id', $lobby->player_one_id)
                        ->lockForUpdate()
                        ->first();

                    if ($playerOne) {
                        $playerOne->increment('credit_balance', (float) $lobby->bet_amount);
                    }
                }

                if ($lobby->status === 'active' && ! $lobby->finished_at) {
                    $betAmount = round((float) $lobby->bet_amount, 2);

                    $playerOne = User::where('id', $lobby->player_one_id)
                        ->lockForUpdate()
                        ->first();

                    $playerTwo = User::where('id', $lobby->player_two_id)
                        ->lockForUpdate()
                        ->first();

                    if ($playerOne) {
                        $playerOne->increment('credit_balance', $betAmount);
                    }

                    if ($playerTwo) {
                        $playerTwo->increment('credit_balance', $betAmount);
                    }
                }

                $lobby->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                    'choice_deadline' => null,
                    'player_one_ready' => false,
                    'player_two_ready' => false,
                ]);
            });

            $this->broadcastLobby($lobby->id);

            return redirect()
                ->route('pokemon-lobby.index')
                ->with('success', 'You left the lobby.');
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'pokemon' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Leave Pokémon lobby failed', [
                'message' => $e->getMessage(),
                'lobby_id' => $lobby->id,
                'user_id' => auth()->id(),
            ]);

            return back()->withErrors([
                'pokemon' => 'Something went wrong while leaving lobby.',
            ]);
        }
    }

    public function cancel(PokemonLobby $lobby)
    {
        return $this->leave($lobby);
    }

    public function pokemonOptions(Request $request)
    {
        $this->guardPlayer();

        $type = strtolower((string) $request->query('type', 'all'));

        try {
            if ($type === 'all') {
                $response = Http::timeout(20)
                    ->acceptJson()
                    ->get('https://pokeapi.co/api/v2/pokemon', [
                        'limit' => 151,
                        'offset' => 0,
                    ]);

                if (! $response->successful()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unable to load Pokémon list.',
                    ], 500);
                }

                $pokemon = collect($response->json('results', []))
                    ->map(function ($item) {
                        return $this->formatPokemonOptionFromUrl($item['name'], $item['url']);
                    })
                    ->filter()
                    ->values()
                    ->all();

                return response()->json([
                    'success' => true,
                    'type' => 'all',
                    'pokemon' => $pokemon,
                ]);
            }

            if ($type === 'earth') {
                $type = 'ground';
            }

            $allowedTypes = [
                'normal', 'fire', 'water', 'electric', 'grass', 'ice',
                'fighting', 'poison', 'ground', 'flying', 'psychic',
                'bug', 'rock', 'ghost', 'dragon',
            ];

            if (! in_array($type, $allowedTypes, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Pokémon type.',
                ], 422);
            }

            $response = Http::timeout(20)
                ->acceptJson()
                ->get('https://pokeapi.co/api/v2/type/' . $type);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to load Pokémon type.',
                ], 500);
            }

            $pokemon = collect($response->json('pokemon', []))
                ->map(function ($item) {
                    $pokemonData = $item['pokemon'] ?? null;

                    if (! $pokemonData) {
                        return null;
                    }

                    return $this->formatPokemonOptionFromUrl($pokemonData['name'], $pokemonData['url']);
                })
                ->filter()
                ->filter(function ($item) {
                    return (int) $item['id'] >= 1 && (int) $item['id'] <= 151;
                })
                ->sortBy('id')
                ->values()
                ->all();

            return response()->json([
                'success' => true,
                'type' => $type,
                'pokemon' => $pokemon,
            ]);
        } catch (\Throwable $e) {
            Log::error('Load Pokémon options failed', [
                'message' => $e->getMessage(),
                'type' => $type,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while loading Pokémon.',
            ], 500);
        }
    }

    private function broadcastLobby(int $lobbyId): void
    {
        try {
            $freshLobby = PokemonLobby::find($lobbyId);

            if ($freshLobby) {
                broadcast(new PokemonLobbyUpdated($freshLobby));
            }
        } catch (\Throwable $e) {
            Log::error('Pokemon lobby broadcast failed', [
                'message' => $e->getMessage(),
                'lobby_id' => $lobbyId,
            ]);
        }
    }

    private function lobbyPayload(PokemonLobby $lobby): array
    {
        return [
            'id' => $lobby->id,
            'status' => $lobby->status,
            'round_number' => (int) $lobby->round_number,
            'player_one_id' => $lobby->player_one_id,
            'player_two_id' => $lobby->player_two_id,
            'player_one_pokemon' => $lobby->player_one_pokemon,
            'player_two_pokemon' => $lobby->player_two_pokemon,
            'player_one_ready' => (bool) $lobby->player_one_ready,
            'player_two_ready' => (bool) $lobby->player_two_ready,
            'player_one_score' => (int) $lobby->player_one_score,
            'player_two_score' => (int) $lobby->player_two_score,
            'winner_id' => $lobby->winner_id,
            'finished_at' => optional($lobby->finished_at)->toDateTimeString(),
            'closed_at' => optional($lobby->closed_at)->toDateTimeString(),
            'pot_amount' => (float) $lobby->pot_amount,
            'payout_amount' => (float) $lobby->payout_amount,
            'gross_payout_amount' => (float) $lobby->gross_payout_amount,
            'commission_amount' => (float) $lobby->commission_amount,
            'agent_commission_amount' => (float) $lobby->agent_commission_amount,
            'company_commission_amount' => (float) $lobby->company_commission_amount,
            'agent_id' => $lobby->agent_id,
            'updated_at' => optional($lobby->updated_at)->timestamp,
        ];
    }

    private function formatPokemonOptionFromUrl(string $name, string $url): ?array
    {
        preg_match('/\/pokemon\/(\d+)\//', $url, $matches);

        $id = $matches[1] ?? null;

        if (! $id) {
            return null;
        }

        return [
            'id' => (int) $id,
            'name' => $name,
            'display_name' => ucfirst(str_replace('-', ' ', $name)),
            'image' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/' . $id . '.png',
        ];
    }

    private function guardPlayer(): void
    {
        if (! auth()->check()) {
            redirect()->route('login')->send();
            exit;
        }

        if (auth()->user()->role !== 'player') {
            abort(403);
        }
    }

    private function isParticipant(PokemonLobby $lobby): bool
    {
        return in_array(auth()->id(), [
            (int) $lobby->player_one_id,
            (int) $lobby->player_two_id,
        ], true);
    }

    private function fetchPokemon(string $nameOrId): ?array
    {
        $response = Http::timeout(15)
            ->acceptJson()
            ->get('https://pokeapi.co/api/v2/pokemon/' . $nameOrId);

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    private function calculatePower(array $pokemon): int
    {
        return collect($pokemon['stats'] ?? [])
            ->sum(function ($stat) {
                return (int) ($stat['base_stat'] ?? 0);
            });
    }

    private function formatPokemon(array $pokemon, int $power): array
    {
        return [
            'id' => $pokemon['id'] ?? null,
            'name' => ucfirst($pokemon['name'] ?? 'Unknown'),
            'image' => data_get($pokemon, 'sprites.other.official-artwork.front_default')
                ?: data_get($pokemon, 'sprites.front_default'),
            'types' => collect($pokemon['types'] ?? [])
                ->map(fn ($type) => ucfirst($type['type']['name'] ?? ''))
                ->filter()
                ->values()
                ->all(),
            'stats' => collect($pokemon['stats'] ?? [])
                ->map(function ($stat) {
                    return [
                        'name' => ucfirst(str_replace('-', ' ', $stat['stat']['name'] ?? '')),
                        'value' => (int) ($stat['base_stat'] ?? 0),
                    ];
                })
                ->values()
                ->all(),
            'power' => $power,
        ];
    }

    private function pokemonNames(): array
    {
        return [];
    }
}