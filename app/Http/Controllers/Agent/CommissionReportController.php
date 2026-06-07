<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommissionReportController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $agentId = auth()->id();

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        | Important:
        | - If date is empty, show OVERALL / ALL-TIME report.
        | - If date is selected, filter only that date.
        */
        $selectedDate = $request->query('date');
        $game = $request->query('game', 'overall');
        $search = $request->query('search');

        /*
        |--------------------------------------------------------------------------
        | Totalizator Commission Query
        |--------------------------------------------------------------------------
        */
        $totalizatorQuery = AgentCommission::query()
            ->with(['agent', 'player', 'bet'])
            ->where('agent_id', $agentId)
            ->where(function ($query) {
                $query->whereNull('side')
                    ->orWhere('side', '!=', 'POKEMON');
            });

        if ($selectedDate) {
            $totalizatorQuery->whereDate('created_at', $selectedDate);
        }

        if ($search) {
            $totalizatorQuery->where(function ($query) use ($search) {
                $query->whereHas('player', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pokémon Commission Query
        |--------------------------------------------------------------------------
        */
        $pokemonQuery = AgentCommission::query()
            ->with(['agent', 'player'])
            ->where('agent_id', $agentId)
            ->where('side', 'POKEMON');

        if ($selectedDate) {
            $pokemonQuery->whereDate('created_at', $selectedDate);
        }

        if ($search) {
            $pokemonQuery->where(function ($query) use ($search) {
                $query->whereHas('player', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })
                ->orWhere('odds', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Totalizator Totals
        |--------------------------------------------------------------------------
        */
        $totalizatorBetAmount = 0;
        $totalizatorAgentCommission = 0;

        if ($game === 'overall' || $game === 'totalizator') {
            $totalizatorBetAmount = (clone $totalizatorQuery)->sum('bet_amount');
            $totalizatorAgentCommission = (clone $totalizatorQuery)->sum('commission_amount');
        }

        /*
        |--------------------------------------------------------------------------
        | Pokémon Totals
        |--------------------------------------------------------------------------
        */
        $pokemonBetAmount = 0;
        $pokemonAgentCommission = 0;

        if ($game === 'overall' || $game === 'pokemon') {
            $pokemonBetAmount = (clone $pokemonQuery)->sum('bet_amount');
            $pokemonAgentCommission = (clone $pokemonQuery)->sum('commission_amount');
        }

        /*
        |--------------------------------------------------------------------------
        | Final Agent Totals
        |--------------------------------------------------------------------------
        */
        $totalBetAmount = $totalizatorBetAmount + $pokemonBetAmount;
        $totalAgentCommission = $totalizatorAgentCommission + $pokemonAgentCommission;
        $totalCommission = $totalAgentCommission;

        /*
        |--------------------------------------------------------------------------
        | Downline Wallet Summary
        |--------------------------------------------------------------------------
        */
        $totalDownlinePlayers = User::where('role', 'player')
            ->where('agent_id', $agentId)
            ->count();

        $totalDownlineWallet = User::where('role', 'player')
            ->where('agent_id', $agentId)
            ->sum('credit_balance');

        $agentWallet = auth()->user()->credit_balance ?? 0;

        /*
        |--------------------------------------------------------------------------
        | Real Commission Wallet
        |--------------------------------------------------------------------------
        | Shows remaining unconverted commission, all-time.
        */
        $agentCommissionWallet = $this->pendingCommissionBalance($agentId);

        /*
        |--------------------------------------------------------------------------
        | Paginated Records
        |--------------------------------------------------------------------------
        | Records still show created_at date and time in the Blade.
        */
        $commissions = collect();

        if ($game === 'overall' || $game === 'totalizator') {
            $commissions = (clone $totalizatorQuery)
                ->latest()
                ->paginate(10, ['*'], 'commission_page')
                ->withQueryString();
        }

        $pokemonCommissions = collect();

        if ($game === 'overall' || $game === 'pokemon') {
            $pokemonRows = (clone $pokemonQuery)
                ->latest()
                ->get()
                ->map(function ($commission) {
                    return $this->makePokemonCommissionRow($commission);
                });

            $pokemonCommissions = $this->paginateCollection(
                $pokemonRows,
                10,
                'pokemon_page'
            );
        }

        return view('agent.commissions.index', [
            'selectedDate' => $selectedDate,
            'game' => $game,
            'search' => $search,

            'commissions' => $commissions,
            'pokemonCommissions' => $pokemonCommissions,

            'totalBetAmount' => $totalBetAmount,
            'totalAgentCommission' => $totalAgentCommission,
            'totalCommission' => $totalCommission,

            'totalizatorBetAmount' => $totalizatorBetAmount,
            'totalizatorAgentCommission' => $totalizatorAgentCommission,

            'pokemonBetAmount' => $pokemonBetAmount,
            'pokemonAgentCommission' => $pokemonAgentCommission,

            'totalCompanyCommission' => 0,
            'totalizatorCompanyCommission' => 0,
            'totalizatorTotalCommission' => $totalizatorAgentCommission,
            'pokemonCompanyCommission' => 0,
            'pokemonTotalCommission' => $pokemonAgentCommission,

            'totalDownlinePlayers' => $totalDownlinePlayers,
            'totalDownlineWallet' => $totalDownlineWallet,
            'agentWallet' => $agentWallet,
            'agentCommissionWallet' => $agentCommissionWallet,
        ]);
    }

    public function convertToWallet(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $requestedAmount = round((float) $validated['amount'], 2);

        try {
            DB::transaction(function () use ($requestedAmount) {
                $agent = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $agent) {
                    throw new \RuntimeException('Agent account not found.');
                }

                $pendingCommissions = AgentCommission::where('agent_id', $agent->id)
                    ->whereRaw('COALESCE(converted_amount, 0) < commission_amount')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $availableBalance = round($pendingCommissions->sum(function ($commission) {
                    $commissionAmount = (float) ($commission->commission_amount ?? 0);
                    $convertedAmount = (float) ($commission->converted_amount ?? 0);

                    return max($commissionAmount - $convertedAmount, 0);
                }), 2);

                if ($availableBalance <= 0) {
                    throw new \RuntimeException('You have no commission balance to convert.');
                }

                if ($requestedAmount > $availableBalance) {
                    throw new \RuntimeException('The amount is higher than your available commission balance.');
                }

                $remainingToConvert = $requestedAmount;

                foreach ($pendingCommissions as $commission) {
                    if ($remainingToConvert <= 0) {
                        break;
                    }

                    $commissionAmount = round((float) ($commission->commission_amount ?? 0), 2);
                    $alreadyConverted = round((float) ($commission->converted_amount ?? 0), 2);
                    $rowAvailable = round(max($commissionAmount - $alreadyConverted, 0), 2);

                    if ($rowAvailable <= 0) {
                        continue;
                    }

                    $amountFromThisRow = min($rowAvailable, $remainingToConvert);
                    $newConvertedAmount = round($alreadyConverted + $amountFromThisRow, 2);

                    $commission->converted_amount = $newConvertedAmount;
                    $commission->conversion_status = $newConvertedAmount >= $commissionAmount
                        ? 'converted'
                        : 'pending';
                    $commission->save();

                    $remainingToConvert = round($remainingToConvert - $amountFromThisRow, 2);
                }

                $agent->increment('credit_balance', $requestedAmount);
            });

            return back()->with('success', 'Commission converted to wallet successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return back()->with('error', 'Something went wrong while converting commission.');
        }
    }

    private function pendingCommissionBalance(int $agentId): float
    {
        return round(
            AgentCommission::where('agent_id', $agentId)
                ->get()
                ->sum(function ($commission) {
                    $commissionAmount = (float) ($commission->commission_amount ?? 0);
                    $convertedAmount = (float) ($commission->converted_amount ?? 0);

                    return max($commissionAmount - $convertedAmount, 0);
                }),
            2
        );
    }

    private function makePokemonCommissionRow(AgentCommission $commission): object
    {
        return (object) [
            'id' => $commission->id,
            'source_game' => 'pokemon',
            'source_id' => $commission->id,

            'player_id' => $commission->player_id,
            'player_name' => $commission->player?->username,
            'player' => $commission->player,

            'event_name' => 'Pokemon Battle Room',
            'round_label' => $commission->odds ?? 'POKEMON',

            'total_bet_amount' => round((float) $commission->bet_amount, 2),

            'gross_payout_amount' => 0,
            'net_payout_amount' => 0,

            'agent_commission_amount' => round((float) $commission->commission_amount, 2),
            'commission_amount' => round((float) $commission->commission_amount, 2),

            'company_commission_amount' => 0,

            'status' => 'settled',
            'settled_at' => $commission->created_at,
        ];
    }

    private function paginateCollection($items, int $perPage = 10, string $pageName = 'page')
    {
        $page = request()->get($pageName, 1);
        $page = max((int) $page, 1);

        $items = collect($items);

        $currentPageItems = $items
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        return new LengthAwarePaginator(
            $currentPageItems,
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
                'query' => request()->query(),
            ]
        );
    }
}