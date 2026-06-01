<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCommission;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $selectedDate = $request->date ?? now()->format('Y-m-d');

        $query = AgentCommission::query()
            ->with(['agent', 'player', 'bet'])
            ->whereDate('created_at', $selectedDate);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($mainQuery) use ($search) {
                $mainQuery->whereHas('player', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })
                ->orWhereHas('agent', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            });
        }

        $totalBetAmount = AgentCommission::whereDate('created_at', $selectedDate)
            ->sum('bet_amount');

        $totalAgentCommission = AgentCommission::whereDate('created_at', $selectedDate)
            ->sum('commission_amount');

        $totalCompanyCommission = AgentCommission::whereDate('created_at', $selectedDate)
            ->sum('company_commission_amount');

        $totalCommission = AgentCommission::whereDate('created_at', $selectedDate)
            ->sum('total_commission_amount');

        $totalPlayerWallet = User::where('role', 'player')
            ->sum('credit_balance');

        $totalAgentWallet = User::where('role', 'agent')
            ->sum('credit_balance');

        $totalAgentAndPlayerWallet = $totalPlayerWallet + $totalAgentWallet;

        /*
        |--------------------------------------------------------------------------
        | Agent Withdraw Report Only
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

        $commissions = $query
            ->latest()
            ->paginate(15, ['*'], 'commission_page')
            ->withQueryString();

        return view('admin.commissions.index', [
            'selectedDate' => $selectedDate,

            'commissions' => $commissions,

            'totalBetAmount' => $totalBetAmount,
            'totalAgentCommission' => $totalAgentCommission,
            'totalCompanyCommission' => $totalCompanyCommission,
            'totalCommission' => $totalCommission,

            'totalPlayerWallet' => $totalPlayerWallet,
            'totalAgentWallet' => $totalAgentWallet,
            'totalAgentAndPlayerWallet' => $totalAgentAndPlayerWallet,

            'agentWithdrawRequests' => $agentWithdrawRequests,
            'totalAgentApprovedWithdraw' => $totalAgentApprovedWithdraw,
            'totalAgentPendingWithdraw' => $totalAgentPendingWithdraw,
            'totalAgentRejectedWithdraw' => $totalAgentRejectedWithdraw,
        ]);
    }
}