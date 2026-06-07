<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PokemonLobby extends Model
{
    protected $fillable = [
        'player_one_id',
        'player_two_id',

        'player_one_pokemon',
        'player_two_pokemon',

        'player_one_power',
        'player_two_power',

        'player_one_data',
        'player_two_data',

        'bet_amount',
        'pot_amount',
        'payout_amount',

        'winner_id',
        'status',

        'choice_deadline',
        'finished_at',
        'closed_at',

        'round_number',
        'player_one_score',
        'player_two_score',

        'player_one_ready',
        'player_two_ready',

        'gross_payout_amount',
        'commission_amount',
        'agent_commission_amount',
        'company_commission_amount',
        'agent_id',
    ];

    protected $casts = [
        'player_one_data' => 'array',
        'player_two_data' => 'array',

        'bet_amount' => 'decimal:2',
        'pot_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',

        'choice_deadline' => 'datetime',
        'finished_at' => 'datetime',
        'closed_at' => 'datetime',

        'player_one_ready' => 'boolean',
        'player_two_ready' => 'boolean',
    ];

    public function playerOne()
    {
        return $this->belongsTo(User::class, 'player_one_id');
    }

    public function playerTwo()
    {
        return $this->belongsTo(User::class, 'player_two_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
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
            'normal',
            'fire',
            'water',
            'electric',
            'grass',
            'ice',
            'fighting',
            'poison',
            'ground',
            'flying',
            'psychic',
            'bug',
            'rock',
            'ghost',
            'dragon',
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
}