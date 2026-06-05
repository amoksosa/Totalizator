<?php

namespace App\Http\Controllers;

use App\Events\CreditBalanceUpdated;
use App\Events\GameWinnerDeclared;
use App\Models\Bet;
use App\Models\GameDeclaration;
use App\Models\GameEvent;
use App\Models\GameRound;
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

        $openEvent = GameEvent::where('created_by', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        $declarations = GameDeclaration::with(['declarer', 'event'])
            ->latest()
            ->paginate(10);

        return view('declare.dashboard', compact('declarations', 'openEvent'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        $request->validate([
            'game_round_id' => ['required', 'exists:game_rounds,id'],
            'winner' => ['required', Rule::in(['MERON', 'WALA', 'DRAW'])],
            'round_code' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $openEvent = GameEvent::where('created_by', auth()->id())
                    ->where('status', 'open')
                    ->lockForUpdate()
                    ->latest()
                    ->first();

                if (! $openEvent) {
                    throw new \RuntimeException('Please create an event first before declaring a match.');
                }

                $round = GameRound::where('id', $request->game_round_id)
                    ->where('game_event_id', $openEvent->id)
                    ->lockForUpdate()
                    ->first();

                if (! $round) {
                    throw new \RuntimeException('Round not found for this event.');
                }

                if ($round->status !== 'closed') {
                    throw new \RuntimeException('Close betting first before declaring the winner.');
                }

                $declaration = GameDeclaration::create([
                    'game_event_id' => $openEvent->id,
                    'declared_by' => auth()->id(),
                    'winner' => $request->winner,
                    'round_code' => $request->round_code ?: $round->round_code,
                ]);

                $pendingBets = Bet::query()
                    ->where('game_round_id', $round->id)
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

                    $grossWinAmount = $this->calculateWinAmount(
                        side: $bet->side,
                        odds: $bet->odds,
                        amount: (float) $bet->amount,
                    );

                    // 5% deduction from the winning amount
                    $winDeductionRate = 5.00;
                    $winDeductionAmount = round($grossWinAmount * ($winDeductionRate / 100), 2);

                    $netWinAmount = round($grossWinAmount - $winDeductionAmount, 2);

                    // Player receives original bet amount + net win amount
                    $payoutAmount = round((float) $bet->amount + $netWinAmount, 2);

                    $player = User::where('id', $bet->user_id)
                        ->lockForUpdate()
                        ->first();

                    if ($player) {
                        $player->increment('credit_balance', $payoutAmount);
                        $updatedPlayerIds[] = $player->id;
                    }

                    $bet->update([
                        'status' => 'won',
                        'win_amount' => $netWinAmount,
                        'payout_amount' => $payoutAmount,
                        'declaration_id' => $declaration->id,
                        'settled_at' => now(),
                    ]);
                }

                $round->update([
                    'status' => 'settled',
                    'winner' => $request->winner,
                    'settled_at' => now(),
                ]);

                return [
                    'declaration' => $declaration,
                    'round' => $round,
                    'updated_player_ids' => array_values(array_unique($updatedPlayerIds)),
                ];
            });

            $this->broadcastDeclaration($result['declaration']);
            $this->broadcastUpdatedPlayerBalances($result['updated_player_ids']);

            return back()->with('success', $request->winner . ' declared successfully. Round is now settled.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
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
            return round($amount * 8, 2);
        }

        $left = (float) $parts[0];
        $right = (float) $parts[1];

        if ($left <= 0 || $right <= 0) {
            return round($amount * 8, 2);
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