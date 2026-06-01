<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\Bet;
use App\Models\GameEvent;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $allEvents = GameEvent::query()
            ->orderByDesc('created_at')
            ->get();

        $events = GameEvent::query()
            ->with('creator')
            ->withCount([
                'declarations',
                'bets',
            ])
            ->when($request->event_id, function ($query, $eventId) {
                $query->where('id', $eventId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('event_name', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $eventTotals = [];

        foreach ($events as $event) {
            $eventTotals[$event->id] = $this->calculateEventTotals($event->id);
        }

        return view('admin.sales.index', compact('events', 'eventTotals', 'allEvents'));
    }

    public function show(GameEvent $event)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $event->load([
            'creator',
            'declarations' => function ($query) {
                $query->with('declarer')->latest();
            },
        ]);

        $totals = $this->calculateEventTotals($event->id);

        $bets = Bet::query()
            ->with(['user.agent'])
            ->where('game_event_id', $event->id)
            ->latest()
            ->paginate(25);

        $commissions = AgentCommission::query()
            ->with(['agent', 'player', 'bet'])
            ->whereHas('bet', function ($query) use ($event) {
                $query->where('game_event_id', $event->id);
            })
            ->latest()
            ->paginate(25, ['*'], 'commissions_page');

        return view('admin.sales.show', compact(
            'event',
            'totals',
            'bets',
            'commissions'
        ));
    }

    private function calculateEventTotals(int $eventId): array
    {
        $totalBets = Bet::where('game_event_id', $eventId)->sum('amount');

        $pendingBets = Bet::where('game_event_id', $eventId)
            ->where('status', 'pending')
            ->sum('amount');

        $wonBets = Bet::where('game_event_id', $eventId)
            ->where('status', 'won')
            ->sum('amount');

        $lostBets = Bet::where('game_event_id', $eventId)
            ->where('status', 'lost')
            ->sum('amount');

        $totalWinAmount = Bet::where('game_event_id', $eventId)
            ->sum('win_amount');

        $totalPayoutAmount = Bet::where('game_event_id', $eventId)
            ->sum('payout_amount');

        $totalAgentCommission = AgentCommission::whereHas('bet', function ($query) use ($eventId) {
            $query->where('game_event_id', $eventId);
        })->sum('commission_amount');

        $totalCompanyCommission = AgentCommission::whereHas('bet', function ($query) use ($eventId) {
            $query->where('game_event_id', $eventId);
        })->sum('company_commission_amount');

        $totalCommission = AgentCommission::whereHas('bet', function ($query) use ($eventId) {
            $query->where('game_event_id', $eventId);
        })->sum('total_commission_amount');

        $netSalesBeforePayout = $totalBets - $totalCommission;
        $netAfterPayout = $totalBets - $totalPayoutAmount - $totalCommission;

        return [
            'total_bets' => $totalBets,
            'pending_bets' => $pendingBets,
            'won_bets' => $wonBets,
            'lost_bets' => $lostBets,
            'total_win_amount' => $totalWinAmount,
            'total_payout_amount' => $totalPayoutAmount,
            'total_agent_commission' => $totalAgentCommission,
            'total_company_commission' => $totalCompanyCommission,
            'total_commission' => $totalCommission,
            'net_sales_before_payout' => $netSalesBeforePayout,
            'net_after_payout' => $netAfterPayout,
        ];
    }
}