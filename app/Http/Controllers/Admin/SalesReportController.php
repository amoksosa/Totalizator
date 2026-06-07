<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\GameSalesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $game = $request->query('game', 'overall');
        $reportType = $request->query('report_type', 'daily');
        $from = $request->query('from', now()->toDateString());
        $to = $request->query('to', now()->toDateString());
        $eventKey = $request->query('event_key');
        $search = $request->query('search');

        $includePokemon = $game === 'overall' || $game === 'pokemon';
        $includeTotalizator = $game === 'overall' || $game === 'totalizator';

        /*
        |--------------------------------------------------------------------------
        | Pokémon Query
        |--------------------------------------------------------------------------
        */
        $pokemonQuery = GameSalesReport::query()
            ->where('source_game', 'pokemon')
            ->whereDate('settled_at', '>=', $from)
            ->whereDate('settled_at', '<=', $to);

        /*
        |--------------------------------------------------------------------------
        | Totalizator Query
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Use Bet records, not AgentCommission records.
        |
        | AgentCommission only records players under agents.
        | Bet records include both:
        | - admin direct players
        | - agent downline players
        */
        $totalizatorQuery = Bet::query()
            ->with(['player.agent', 'event'])
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        /*
        |--------------------------------------------------------------------------
        | Event Filter
        |--------------------------------------------------------------------------
        | Format:
        | pokemon|12
        | totalizator|5
        */
        if ($eventKey) {
            [$eventGame, $eventSourceId] = array_pad(explode('|', $eventKey), 2, null);

            if ($eventGame === 'pokemon' && $eventSourceId) {
                $pokemonQuery->where('source_id', $eventSourceId);
                $includePokemon = true;
                $includeTotalizator = false;
            }

            if ($eventGame === 'totalizator' && $eventSourceId) {
                $totalizatorQuery->where('game_event_id', $eventSourceId);
                $includePokemon = false;
                $includeTotalizator = true;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($search) {
            $pokemonQuery->where(function ($query) use ($search) {
                $query->where('event_name', 'like', "%{$search}%")
                    ->orWhere('round_label', 'like', "%{$search}%");
            });

            $totalizatorQuery->where(function ($query) use ($search) {
                $query->whereHas('player', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })
                ->orWhereHas('player.agent', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })
                ->orWhereHas('event', function ($q) use ($search) {
                    $q->where('event_name', 'like', "%{$search}%");
                });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | All Events for Filter Dropdown
        |--------------------------------------------------------------------------
        */
        $allEvents = collect();

        $pokemonEvents = GameSalesReport::query()
        ->where('source_game', 'pokemon')
        ->whereNotNull('source_id')
        ->select('source_id', 'event_name')
        ->groupBy('source_id', 'event_name')
        ->get()
        ->map(function ($row) {
            return (object) [
                'source_game' => 'pokemon',
                'source_id' => $row->source_id,
                'event_name' => $row->event_name ?? 'Pokémon Event #' . $row->source_id,
            ];
        });

        $totalizatorEvents = Bet::query()
            ->with('event')
            ->whereNotNull('game_event_id')
            ->select('game_event_id')
            ->groupBy('game_event_id')
            ->get()
            ->map(function ($row) {
                return (object) [
                    'source_game' => 'totalizator',
                    'source_id' => $row->game_event_id,
                    'event_name' => $row->event?->event_name ?? 'Totalizator Event #' . $row->game_event_id,
                ];
            });

        $allEvents = $pokemonEvents
            ->merge($totalizatorEvents)
            ->sortBy('event_name')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Pokémon Summary
        |--------------------------------------------------------------------------
        */
        $pokemonSummary = (object) [
            'total_records' => 0,
            'total_bets' => 0,
            'gross_payout' => 0,
            'net_payout' => 0,
            'total_commission' => 0,
            'agent_commission' => 0,
            'company_commission' => 0,
        ];

        if ($includePokemon) {
            $pokemonSummary = (clone $pokemonQuery)
                ->selectRaw('
                    COUNT(*) as total_records,
                    COALESCE(SUM(total_bet_amount), 0) as total_bets,
                    COALESCE(SUM(gross_payout_amount), 0) as gross_payout,
                    COALESCE(SUM(net_payout_amount), 0) as net_payout,
                    COALESCE(SUM(commission_amount), 0) as total_commission,
                    COALESCE(SUM(agent_commission_amount), 0) as agent_commission,
                    COALESCE(SUM(company_commission_amount), 0) as company_commission
                ')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Totalizator Summary
        |--------------------------------------------------------------------------
        */
        $totalizatorRows = collect();

        if ($includeTotalizator) {
            $totalizatorRows = (clone $totalizatorQuery)
                ->latest()
                ->get()
                ->map(function ($bet) {
                    return $this->makeTotalizatorSalesRow($bet);
                });
        }

        $totalizatorSummary = (object) [
            'total_records' => $totalizatorRows->count(),
            'total_bets' => $totalizatorRows->sum('total_bets'),
            'gross_payout' => $totalizatorRows->sum('gross_payout'),
            'net_payout' => $totalizatorRows->sum('net_payout'),
            'total_commission' => $totalizatorRows->sum('total_commission'),
            'agent_commission' => $totalizatorRows->sum('agent_commission'),
            'company_commission' => $totalizatorRows->sum('company_commission'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Overall Summary
        |--------------------------------------------------------------------------
        */
        $summary = (object) [
            'total_records' => (int) ($pokemonSummary->total_records ?? 0) + (int) ($totalizatorSummary->total_records ?? 0),
            'total_bets' => (float) ($pokemonSummary->total_bets ?? 0) + (float) ($totalizatorSummary->total_bets ?? 0),
            'gross_payout' => (float) ($pokemonSummary->gross_payout ?? 0) + (float) ($totalizatorSummary->gross_payout ?? 0),
            'net_payout' => (float) ($pokemonSummary->net_payout ?? 0) + (float) ($totalizatorSummary->net_payout ?? 0),
            'total_commission' => (float) ($pokemonSummary->total_commission ?? 0) + (float) ($totalizatorSummary->total_commission ?? 0),
            'agent_commission' => (float) ($pokemonSummary->agent_commission ?? 0) + (float) ($totalizatorSummary->agent_commission ?? 0),
            'company_commission' => (float) ($pokemonSummary->company_commission ?? 0) + (float) ($totalizatorSummary->company_commission ?? 0),
        ];

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */
        $reports = collect();

        if ($reportType === 'event') {
            /*
            |--------------------------------------------------------------------------
            | Event-Based Pokémon Report
            |--------------------------------------------------------------------------
            */
            if ($includePokemon) {
                $pokemonReports = (clone $pokemonQuery)
                    ->selectRaw('
                        source_game,
                        source_id,
                        event_name,
                        COUNT(*) as total_records,
                        COALESCE(SUM(total_bet_amount), 0) as total_bets,
                        COALESCE(SUM(gross_payout_amount), 0) as gross_payout,
                        COALESCE(SUM(net_payout_amount), 0) as net_payout,
                        COALESCE(SUM(commission_amount), 0) as total_commission,
                        COALESCE(SUM(agent_commission_amount), 0) as agent_commission,
                        COALESCE(SUM(company_commission_amount), 0) as company_commission
                    ')
                    ->groupBy('source_game', 'source_id', 'event_name')
                    ->get();

                $reports = $reports->merge($pokemonReports);
            }

            /*
            |--------------------------------------------------------------------------
            | Event-Based Totalizator Report
            |--------------------------------------------------------------------------
            */
            if ($includeTotalizator) {
                $totalizatorReports = $totalizatorRows
                    ->groupBy('source_id')
                    ->map(function ($rows, $sourceId) {
                        $first = $rows->first();

                        return (object) [
                            'source_game' => 'totalizator',
                            'source_id' => $sourceId,
                            'event_name' => $first->event_name,
                            'total_records' => $rows->sum('total_records'),
                            'total_bets' => $rows->sum('total_bets'),
                            'gross_payout' => $rows->sum('gross_payout'),
                            'net_payout' => $rows->sum('net_payout'),
                            'total_commission' => $rows->sum('total_commission'),
                            'agent_commission' => $rows->sum('agent_commission'),
                            'company_commission' => $rows->sum('company_commission'),
                        ];
                    })
                    ->values();

                $reports = $reports->merge($totalizatorReports);
            }

            $reports = $reports
                ->sortBy('event_name')
                ->values();
        } else {
            /*
            |--------------------------------------------------------------------------
            | Daily Pokémon Report
            |--------------------------------------------------------------------------
            */
            if ($includePokemon) {
                $pokemonDailyReports = (clone $pokemonQuery)
                    ->selectRaw('
                        DATE(settled_at) as report_date,
                        source_game,
                        COUNT(*) as total_records,
                        COALESCE(SUM(total_bet_amount), 0) as total_bets,
                        COALESCE(SUM(gross_payout_amount), 0) as gross_payout,
                        COALESCE(SUM(net_payout_amount), 0) as net_payout,
                        COALESCE(SUM(commission_amount), 0) as total_commission,
                        COALESCE(SUM(agent_commission_amount), 0) as agent_commission,
                        COALESCE(SUM(company_commission_amount), 0) as company_commission
                    ')
                    ->groupBy(DB::raw('DATE(settled_at)'), 'source_game')
                    ->get();

                $reports = $reports->merge($pokemonDailyReports);
            }

            /*
            |--------------------------------------------------------------------------
            | Daily Totalizator Report
            |--------------------------------------------------------------------------
            */
            if ($includeTotalizator) {
                $totalizatorDailyReports = $totalizatorRows
                    ->groupBy('report_date')
                    ->map(function ($rows, $date) {
                        return (object) [
                            'report_date' => $date,
                            'source_game' => 'totalizator',
                            'total_records' => $rows->sum('total_records'),
                            'total_bets' => $rows->sum('total_bets'),
                            'gross_payout' => $rows->sum('gross_payout'),
                            'net_payout' => $rows->sum('net_payout'),
                            'total_commission' => $rows->sum('total_commission'),
                            'agent_commission' => $rows->sum('agent_commission'),
                            'company_commission' => $rows->sum('company_commission'),
                        ];
                    })
                    ->values();

                $reports = $reports->merge($totalizatorDailyReports);
            }

            $reports = $reports
                ->sortByDesc('report_date')
                ->values();
        }

        return view('admin.sales-reports.index', [
            'game' => $game,
            'reportType' => $reportType,
            'allEvents' => $allEvents,
            'eventKey' => $eventKey,
            'from' => $from,
            'to' => $to,
            'search' => $search,
            'summary' => $summary,
            'reports' => $reports,
        ]);
    }

    public function show(Request $request, $event)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return redirect()
            ->route('admin.sales.index', [
                'report_type' => 'event',
                'event_key' => $event,
                'from' => $request->query('from', now()->toDateString()),
                'to' => $request->query('to', now()->toDateString()),
            ]);
    }

    private function makeTotalizatorSalesRow(Bet $bet): object
    {
        $acceptedAmount = $this->acceptedBetAmount($bet);

        /*
        |--------------------------------------------------------------------------
        | Admin Sales Report Commission Rule
        |--------------------------------------------------------------------------
        | For every accepted Totalizator bet:
        |
        | Agent/Admin Commission = 3%
        | Company Commission = 2%
        | Total Commission = 5%
        |
        | This applies to:
        | - admin direct players
        | - agent downline players
        */
        $agentCommission = round($acceptedAmount * 0.03, 2);
        $companyCommission = round($acceptedAmount * 0.02, 2);
        $totalCommission = round($acceptedAmount * 0.05, 2);

        $eventId = $bet->game_event_id ?: 0;
        $eventName = $bet->event?->event_name ?? 'Totalizator Event #' . ($eventId ?: 'No Event');

        return (object) [
            'source_game' => 'totalizator',
            'source_id' => $eventId,
            'event_name' => $eventName,

            'report_date' => $bet->created_at?->format('Y-m-d'),

            'total_records' => 1,
            'total_bets' => $acceptedAmount,

            'gross_payout' => (float) ($bet->payout_amount ?? 0),
            'net_payout' => (float) ($bet->win_amount ?? 0),

            'total_commission' => $totalCommission,
            'agent_commission' => $agentCommission,
            'company_commission' => $companyCommission,
        ];
    }

    private function acceptedBetAmount(Bet $bet): float
    {
        $amount = (float) ($bet->amount ?? 0);
        $refundedAmount = (float) ($bet->refunded_amount ?? 0);

        return round(max($amount - $refundedAmount, 0), 2);
    }
}