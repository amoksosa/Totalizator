<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $query = AgentCommission::query()
            ->with(['player', 'bet'])
            ->where('agent_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->search;

            $query->whereHas('player', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        $totalCommission = AgentCommission::where('agent_id', auth()->id())
            ->sum('commission_amount');

        $todayCommission = AgentCommission::where('agent_id', auth()->id())
            ->whereDate('created_at', today())
            ->sum('commission_amount');

        $totalBetAmount = AgentCommission::where('agent_id', auth()->id())
            ->sum('bet_amount');

        $todayBetAmount = AgentCommission::where('agent_id', auth()->id())
            ->whereDate('created_at', today())
            ->sum('bet_amount');

        $commissions = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('agent.commissions.index', [
            'commissions' => $commissions,
            'totalCommission' => $totalCommission,
            'todayCommission' => $todayCommission,
            'totalBetAmount' => $totalBetAmount,
            'todayBetAmount' => $todayBetAmount,
        ]);
    }
}