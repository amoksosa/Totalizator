<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PokemonGameController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        return view('player.pokemon-game');
    }

    public function battle(Request $request)
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        $validated = $request->validate([
            'pokemon' => ['required', 'string', 'max:50'],
            'bet_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $pokemonName = strtolower(trim($validated['pokemon']));
        $betAmount = round((float) $validated['bet_amount'], 2);

        $player = User::where('id', auth()->id())->first();

        if (! $player) {
            return response()->json([
                'success' => false,
                'message' => 'Player not found.',
            ], 404);
        }

        if ((float) $player->credit_balance < $betAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient credit balance.',
            ], 422);
        }

        try {
            $playerPokemon = $this->fetchPokemon($pokemonName);

            if (! $playerPokemon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pokémon not found. Try pikachu, charizard, bulbasaur, squirtle, or mewtwo.',
                ], 404);
            }

            $enemyId = random_int(1, 151);
            $enemyPokemon = $this->fetchPokemon((string) $enemyId);

            if (! $enemyPokemon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enemy Pokémon could not be loaded.',
                ], 500);
            }

            $playerPower = $this->calculatePower($playerPokemon);
            $enemyPower = $this->calculatePower($enemyPokemon);

            $balanceBefore = (float) $player->credit_balance;
            $winAmount = 0;
            $result = 'lost';

            if ($playerPower > $enemyPower) {
                $winAmount = round($betAmount * 1.8, 2);
                $result = 'won';
            } elseif ($playerPower === $enemyPower) {
                $winAmount = $betAmount;
                $result = 'draw';
            }

            $newBalance = round($balanceBefore - $betAmount + $winAmount, 2);

            $player->update([
                'credit_balance' => $newBalance,
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'bet_amount' => number_format($betAmount, 2, '.', ''),
                'win_amount' => number_format($winAmount, 2, '.', ''),
                'balance_before' => number_format($balanceBefore, 2, '.', ''),
                'new_balance' => number_format($newBalance, 2, '.', ''),

                'player_pokemon' => $this->formatPokemon($playerPokemon, $playerPower),
                'enemy_pokemon' => $this->formatPokemon($enemyPokemon, $enemyPower),
            ]);
        } catch (\Throwable $e) {
            Log::error('Pokemon battle failed', [
                'message' => $e->getMessage(),
                'pokemon' => $pokemonName,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while loading Pokémon data.',
                'error' => $e->getMessage(),
            ], 500);
        }
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
}