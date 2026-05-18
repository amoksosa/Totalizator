<?php

namespace App\Http\Controllers;

use App\Events\CreditBalanceUpdated;
use App\Events\GameWinnerDeclared;
use App\Models\Bet;
use App\Models\GameDeclaration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DeclareController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $declarations = GameDeclaration::with('declarer')
            ->latest()
            ->paginate(10);

        return view('declare.dashboard', compact('declarations'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $request->validate([
            'winner' => ['required', Rule::in(['MERON', 'WALA', 'DRAW'])],
            'round_code' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $declaration = GameDeclaration::create([
                    'declared_by' => auth()->id(),
                    'winner' => $request->winner,
                    'round_code' => $request->round_code,
                ]);

                $pendingBets = Bet::query()
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->get();

                $updatedPlayerIds = [];

                foreach ($pendingBets as $bet) {
                    $isWinner = $bet->side === $request->winner;

                    if (! $isWinner) {
                        $bet->update([
                            'status' => 'lost',
                            'win_amount' => 0,
                            'payout_amount' => 0,
                            'declaration_id' => $declaration->id,
                            'settled_at' => now(),
                        ]);

                        continue;
                    }

                    $winAmount = $this->calculateWinAmount(
                        side: $bet->side,
                        odds: $bet->odds,
                        amount: (float) $bet->amount,
                    );

                    $payoutAmount = (float) $bet->amount + $winAmount;

                    $player = User::where('id', $bet->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($player) {
                        $player->increment('credit_balance', $payoutAmount);
                        $updatedPlayerIds[] = $player->id;
                    }

                    $bet->update([
                        'status' => 'won',
                        'win_amount' => $winAmount,
                        'payout_amount' => $payoutAmount,
                        'declaration_id' => $declaration->id,
                        'settled_at' => now(),
                    ]);
                }

                return [
                    'declaration' => $declaration,
                    'updated_player_ids' => array_values(array_unique($updatedPlayerIds)),
                ];
            });

            $declaration = $result['declaration'];

            $this->broadcastDeclaration($declaration);
            $this->broadcastUpdatedPlayerBalances($result['updated_player_ids']);

            return back()->with('success', $request->winner . ' declared and bets settled successfully.');
        } catch (\Throwable $e) {
            Log::error('Winner declaration settlement failed', [
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Something went wrong while declaring winner.');
        }
    }

    private function calculateWinAmount(string $side, string $odds, float $amount): float
    {
        if ($side === 'DRAW') {
            return $this->calculateDrawWinAmount($odds, $amount);
        }

        return $this->calculateMeronWalaWinAmount($side, $odds, $amount);
    }

    private function calculateMeronWalaWinAmount(string $side, string $odds, float $amount): float
    {
        $parts = explode('-', $odds);

        if (count($parts) !== 2) {
            return $amount;
        }

        $left = (float) $parts[0];
        $right = (float) $parts[1];

        if ($left <= 0 || $right <= 0) {
            return $amount;
        }

        if ($side === 'MERON') {
            return round($amount * ($right / $left), 2);
        }

        if ($side === 'WALA') {
            return round($amount * ($left / $right), 2);
        }

        return $amount;
    }

    private function calculateDrawWinAmount(string $odds, float $amount): float
    {
        $parts = explode('-', $odds);

        if (count($parts) !== 2) {
            return $amount * 8;
        }

        $left = (float) $parts[0];
        $right = (float) $parts[1];

        if ($left <= 0 || $right <= 0) {
            return $amount * 8;
        }

        return round($amount * ($right / $left), 2);
    }

    private function broadcastDeclaration(GameDeclaration $declaration): void
    {
        try {
            broadcast(new GameWinnerDeclared($declaration));
        } catch (\Throwable $e) {
            Log::error('Winner declaration broadcast failed', [
                'message' => $e->getMessage(),
                'declaration_id' => $declaration->id,
            ]);
        }
    }

    private function broadcastUpdatedPlayerBalances(array $playerIds): void
    {
        foreach ($playerIds as $playerId) {
            $player = User::find($playerId);

            if (! $player) {
                continue;
            }

            try {
                broadcast(new CreditBalanceUpdated($player));
            } catch (\Throwable $e) {
                Log::error('Player settlement balance broadcast failed', [
                    'message' => $e->getMessage(),
                    'player_id' => $player->id,
                ]);
            }
        }
    }
}