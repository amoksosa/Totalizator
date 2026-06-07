<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\GameSalesReport;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $game = $request->query('game', 'overall');
        $search = $request->query('search');

        /*
        |--------------------------------------------------------------------------
        | Totalizator Bets Query
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Admin report should use GameBet, not AgentCommission only.
        |
        | Why:
        | - AgentCommission only records bets from players under agents.
        | - Admin direct players usually have agent_id = null.
        | - So admin direct player bets will be missing if we only use AgentCommission.
        */
        $totalizatorBetQuery = Bet::query()
            ->with(['player.agent'])
            ->whereDate('created_at', $selectedDate);

        if ($search) {
            $totalizatorBetQuery->where(function ($mainQuery) use ($search) {
                $mainQuery->whereHas('player', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })
                ->orWhereHas('player.agent', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Pokémon Commission Query
        |--------------------------------------------------------------------------
        | Pokémon reports are stored in game_sales_reports.
        */
        $pokemonQuery = GameSalesReport::query()
            ->where('source_game', 'pokemon')
            ->whereDate('settled_at', $selectedDate);

        if ($search) {
            $pokemonQuery->where(function ($query) use ($search) {
                $query->where('event_name', 'like', "%{$search}%")
                    ->orWhere('round_label', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Totals by Game Filter
        |--------------------------------------------------------------------------
        */
        $totalizatorBetAmount = 0;
        $totalizatorAgentCommission = 0;
        $totalizatorCompanyCommission = 0;
        $totalizatorTotalCommission = 0;

        $pokemonBetAmount = 0;
        $pokemonAgentCommission = 0;
        $pokemonCompanyCommission = 0;
        $pokemonTotalCommission = 0;

        $totalizatorRows = collect();

        if ($game === 'overall' || $game === 'totalizator') {
            $totalizatorBets = (clone $totalizatorBetQuery)
                ->latest()
                ->get();

            $totalizatorRows = $totalizatorBets->map(function ($bet) {
                return $this->makeTotalizatorCommissionRow($bet);
            });

            $totalizatorBetAmount = $totalizatorRows->sum('bet_amount');
            $totalizatorAgentCommission = $totalizatorRows->sum('commission_amount');
            $totalizatorCompanyCommission = $totalizatorRows->sum('company_commission_amount');
            $totalizatorTotalCommission = $totalizatorRows->sum('total_commission_amount');
        }

        if ($game === 'overall' || $game === 'pokemon') {
            $pokemonBetAmount = (clone $pokemonQuery)->sum('total_bet_amount');
            $pokemonAgentCommission = (clone $pokemonQuery)->sum('agent_commission_amount');
            $pokemonCompanyCommission = (clone $pokemonQuery)->sum('company_commission_amount');
            $pokemonTotalCommission = (clone $pokemonQuery)->sum('commission_amount');
        }

        $totalBetAmount = $totalizatorBetAmount + $pokemonBetAmount;
        $totalAgentCommission = $totalizatorAgentCommission + $pokemonAgentCommission;
        $totalCompanyCommission = $totalizatorCompanyCommission + $pokemonCompanyCommission;
        $totalCommission = $totalizatorTotalCommission + $pokemonTotalCommission;

        /*
        |--------------------------------------------------------------------------
        | Wallet Totals
        |--------------------------------------------------------------------------
        */
        $totalPlayerWallet = User::where('role', 'player')
            ->sum('credit_balance');

        $totalAgentWallet = User::where('role', 'agent')
            ->sum('credit_balance');

        $totalAgentAndPlayerWallet = $totalPlayerWallet + $totalAgentWallet;

        /*
        |--------------------------------------------------------------------------
        | Agent Withdraw Requests
        |--------------------------------------------------------------------------
        */
        $agentWithdrawQuery = WithdrawRequest::query()
            ->with('user')
            ->whereDate('created_at', $selectedDate)
            ->whereHas('user', function ($query) {
                $query->where('role', 'agent');
            });

        $totalAgentApprovedWithdraw = (clone $agentWithdrawQuery)
            ->where('status', 'approved')
            ->sum('amount');

        $totalAgentPendingWithdraw = (clone $agentWithdrawQuery)
            ->where('status', 'pending')
            ->sum('amount');

        $totalAgentRejectedWithdraw = (clone $agentWithdrawQuery)
            ->where('status', 'rejected')
            ->sum('amount');

        $agentWithdrawRequests = (clone $agentWithdrawQuery)
            ->latest()
            ->paginate(10, ['*'], 'withdraw_page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Records
        |--------------------------------------------------------------------------
        */
        $commissions = collect();
        $pokemonCommissions = collect();

        if ($game === 'overall' || $game === 'totalizator') {
            $commissions = $this->paginateCollection(
                $totalizatorRows->sortByDesc('created_at')->values(),
                15,
                'commission_page'
            );
        }

        if ($game === 'overall' || $game === 'pokemon') {
            $pokemonCommissions = (clone $pokemonQuery)
                ->latest('settled_at')
                ->paginate(15, ['*'], 'pokemon_page')
                ->withQueryString();
        }

        return view('admin.commissions.index', [
            'selectedDate' => $selectedDate,
            'game' => $game,
            'search' => $search,

            'commissions' => $commissions,
            'pokemonCommissions' => $pokemonCommissions,

            'totalBetAmount' => $totalBetAmount,
            'totalAgentCommission' => $totalAgentCommission,
            'totalCompanyCommission' => $totalCompanyCommission,
            'totalCommission' => $totalCommission,

            'totalizatorBetAmount' => $totalizatorBetAmount,
            'totalizatorAgentCommission' => $totalizatorAgentCommission,
            'totalizatorCompanyCommission' => $totalizatorCompanyCommission,
            'totalizatorTotalCommission' => $totalizatorTotalCommission,

            'pokemonBetAmount' => $pokemonBetAmount,
            'pokemonAgentCommission' => $pokemonAgentCommission,
            'pokemonCompanyCommission' => $pokemonCompanyCommission,
            'pokemonTotalCommission' => $pokemonTotalCommission,

            'totalPlayerWallet' => $totalPlayerWallet,
            'totalAgentWallet' => $totalAgentWallet,
            'totalAgentAndPlayerWallet' => $totalAgentAndPlayerWallet,

            'agentWithdrawRequests' => $agentWithdrawRequests,
            'totalAgentApprovedWithdraw' => $totalAgentApprovedWithdraw,
            'totalAgentPendingWithdraw' => $totalAgentPendingWithdraw,
            'totalAgentRejectedWithdraw' => $totalAgentRejectedWithdraw,
        ]);
    }

    private function makeTotalizatorCommissionRow(Bet $bet): object
{
    $player = $bet->player;
    $agent = $player?->agent;

    $rawAmount = (float) ($bet->amount ?? 0);
    $refundedAmount = (float) ($bet->refunded_amount ?? 0);

    $betAmount = round(max($rawAmount - $refundedAmount, 0), 2);

    /*
    |--------------------------------------------------------------------------
    | Admin Report Commission Rule
    |--------------------------------------------------------------------------
    | Every accepted totalizator bet should be split like this:
    |
    | - Agent/Admin commission: 3%
    | - Company commission: 2%
    | - Total commission: 5%
    |
    | This applies to both:
    | - players under agents
    | - admin direct players
    */
    $agentCommissionRate = 0.03;
    $companyCommissionRate = 0.02;
    $totalCommissionRate = 0.05;

    $agentCommissionAmount = round($betAmount * $agentCommissionRate, 2);
    $companyCommissionAmount = round($betAmount * $companyCommissionRate, 2);
    $totalCommissionAmount = round($betAmount * $totalCommissionRate, 2);

    return (object) [
        'id' => $bet->id,

        'agent_id' => $agent?->id,
        'player_id' => $player?->id,
        'bet_id' => $bet->id,

        'agent' => $agent,
        'player' => $player,
        'bet' => $bet,

        'bet_amount' => $betAmount,

        'commission_rate' => $agentCommissionRate * 100,
        'commission_amount' => $agentCommissionAmount,

        'company_commission_rate' => $companyCommissionRate * 100,
        'company_commission_amount' => $companyCommissionAmount,

        'total_commission_rate' => $totalCommissionRate * 100,
        'total_commission_amount' => $totalCommissionAmount,

        'side' => $bet->side ?? null,
        'odds' => $bet->odds ?? null,

        'conversion_status' => null,
        'converted_amount' => 0,

        'created_at' => $bet->created_at,
        'updated_at' => $bet->updated_at,

        'commission_source' => $agent ? 'agent_downline' : 'admin_direct',
        'commission_source_label' => $agent ? 'Agent Downline' : 'Admin Direct Player',
    ];
}

    private function paginateCollection($items, int $perPage = 15, string $pageName = 'page')
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