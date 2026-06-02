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
            $eventTotals[$event->id] = $this->calculateEventTotals($event);
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

        $totals = $this->calculateEventTotals($event);

        $bets = $this->eventBetsQuery($event)
            ->with(['user.agent'])
            ->latest()
            ->paginate(25);

        $commissions = AgentCommission::query()
            ->with(['agent', 'player', 'bet'])
            ->whereHas('bet', function ($query) use ($event) {
                $query->where(function ($betQuery) use ($event) {
                    $betQuery->where('game_event_id', $event->id)
                        ->orWhere(function ($oldBetQuery) use ($event) {
                            $oldBetQuery->whereNull('game_event_id')
                                ->whereDate('created_at', $event->event_date);
                        });
                });
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

    private function eventBetsQuery(GameEvent $event)
    {
        return Bet::query()
            ->where(function ($query) use ($event) {
                $query->where('game_event_id', $event->id)
                    ->orWhere(function ($oldBetQuery) use ($event) {
                        $oldBetQuery->whereNull('game_event_id')
                            ->whereDate('created_at', $event->event_date);
                    });
            });
    }

    private function eventCommissionsQuery(GameEvent $event)
    {
        return AgentCommission::query()
            ->whereHas('bet', function ($query) use ($event) {
                $query->where(function ($betQuery) use ($event) {
                    $betQuery->where('game_event_id', $event->id)
                        ->orWhere(function ($oldBetQuery) use ($event) {
                            $oldBetQuery->whereNull('game_event_id')
                                ->whereDate('created_at', $event->event_date);
                        });
                });
            });
    }

    private function calculateEventTotals(GameEvent $event): array
    {
        $totalBets = (clone $this->eventBetsQuery($event))
            ->sum('amount');

        $pendingBets = (clone $this->eventBetsQuery($event))
            ->where('status', 'pending')
            ->sum('amount');

        $wonBets = (clone $this->eventBetsQuery($event))
            ->where('status', 'won')
            ->sum('amount');

        $lostBets = (clone $this->eventBetsQuery($event))
            ->where('status', 'lost')
            ->sum('amount');

        $totalWinAmount = (clone $this->eventBetsQuery($event))
            ->sum('win_amount');

        $totalPayoutAmount = (clone $this->eventBetsQuery($event))
            ->sum('payout_amount');

        $totalAgentCommission = (clone $this->eventCommissionsQuery($event))
            ->sum('commission_amount');

        $totalCompanyCommission = (clone $this->eventCommissionsQuery($event))
            ->sum('company_commission_amount');

        $totalCommission = (clone $this->eventCommissionsQuery($event))
            ->sum('total_commission_amount');

        $betRecords = (clone $this->eventBetsQuery($event))
            ->count();

        $commissionRecords = (clone $this->eventCommissionsQuery($event))
            ->count();

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
            'bet_records' => $betRecords,
            'commission_records' => $commissionRecords,
            'net_sales_before_payout' => $netSalesBeforePayout,
            'net_after_payout' => $netAfterPayout,
        ];
    }
}