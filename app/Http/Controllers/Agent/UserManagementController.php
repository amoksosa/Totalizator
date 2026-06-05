<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $players = User::where('role', 'player')
            ->where('agent_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('agent.users.index', compact('players'));
    }

    public function giveCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        if ($user->role !== 'player' || (int) $user->agent_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = round((float) $validated['credit_amount'], 2);
        $agent = auth()->user();

        if ((float) $agent->credit_balance < $amount) {
            return back()->withErrors([
                'credit_amount' => 'Insufficient agent credit balance.',
            ]);
        }

        DB::transaction(function () use ($agent, $user, $amount) {
            $agent = User::where('id', $agent->id)->lockForUpdate()->first();
            $player = User::where('id', $user->id)->lockForUpdate()->first();

            $agentPreviousBalance = (float) $agent->credit_balance;
            $playerPreviousBalance = (float) $player->credit_balance;

            $agent->decrement('credit_balance', $amount);
            $player->increment('credit_balance', $amount);

            $agent->refresh();
            $player->refresh();

            CreditTransaction::create([
                'user_id' => $player->id,
                'agent_id' => $agent->id,
                'type' => 'agent_give_credit',
                'amount' => $amount,
                'previous_balance' => $playerPreviousBalance,
                'current_balance' => $player->credit_balance,
                'description' => 'Agent gave credit to player.',
            ]);

            CreditTransaction::create([
                'user_id' => $agent->id,
                'agent_id' => $agent->id,
                'type' => 'agent_credit_deduct',
                'amount' => $amount,
                'previous_balance' => $agentPreviousBalance,
                'current_balance' => $agent->credit_balance,
                'description' => 'Credit deducted after giving credit to player.',
            ]);
        });

        return back()->with('success', 'Credit given successfully.');
    }

    public function getCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        if ($user->role !== 'player' || (int) $user->agent_id !== (int) auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = round((float) $validated['credit_amount'], 2);

        if ((float) $user->credit_balance < $amount) {
            return back()->withErrors([
                'credit_amount' => 'Player has insufficient credit balance.',
            ]);
        }

        DB::transaction(function () use ($user, $amount) {
            $agent = User::where('id', auth()->id())->lockForUpdate()->first();
            $player = User::where('id', $user->id)->lockForUpdate()->first();

            $agentPreviousBalance = (float) $agent->credit_balance;
            $playerPreviousBalance = (float) $player->credit_balance;

            $player->decrement('credit_balance', $amount);
            $agent->increment('credit_balance', $amount);

            $agent->refresh();
            $player->refresh();

            CreditTransaction::create([
                'user_id' => $player->id,
                'agent_id' => $agent->id,
                'type' => 'agent_get_credit',
                'amount' => $amount,
                'previous_balance' => $playerPreviousBalance,
                'current_balance' => $player->credit_balance,
                'description' => 'Agent retrieved credit from player.',
            ]);

            CreditTransaction::create([
                'user_id' => $agent->id,
                'agent_id' => $agent->id,
                'type' => 'agent_credit_received',
                'amount' => $amount,
                'previous_balance' => $agentPreviousBalance,
                'current_balance' => $agent->credit_balance,
                'description' => 'Agent received credit from player.',
            ]);
        });

        return back()->with('success', 'Credit retrieved successfully.');
    }
}