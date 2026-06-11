<?php

namespace App\Http\Controllers\Agent;

use App\Events\CreditBalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawRequestController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $withdrawRequests = WithdrawRequest::query()
            ->with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'player')
                    ->where('agent_id', auth()->id());
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('agent.withdrawals.index', compact('withdrawRequests'));
    }

    public function approve(Request $request, WithdrawRequest $withdrawRequest)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $agent = DB::transaction(function () use ($request, $withdrawRequest) {
                $agent = User::where('id', auth()->id())
                    ->lockForUpdate()
                    ->first();

                if (! $agent || $agent->role !== 'agent') {
                    throw new \RuntimeException('Unauthorized agent account.');
                }

                $withdrawRequest = WithdrawRequest::where('id', $withdrawRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $withdrawRequest || $withdrawRequest->status !== 'pending') {
                    throw new \RuntimeException('This withdraw request is already processed.');
                }

                $player = User::where('id', $withdrawRequest->user_id)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $player ||
                    $player->role !== 'player' ||
                    (int) $player->agent_id !== (int) $agent->id
                ) {
                    throw new \RuntimeException('You can only approve withdrawal requests from your own players.');
                }

                /*
                 * Player withdrawal approved.
                
                 */
                $agent->increment('credit_balance', $withdrawRequest->amount);
                $agent->refresh();

                $withdrawRequest->update([
                    'status' => 'approved',
                    'admin_note' => $request->admin_note,
                    'agent_id' => $agent->id,
                    'approved_at' => now(),
                ]);

                return $agent;
            });

            try {
                broadcast(new CreditBalanceUpdated($agent));
            } catch (\Throwable $broadcastError) {
                Log::error('Agent withdraw approve credit broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'agent_id' => $agent->id,
                ]);
            }

            return back()->with('success', 'Player withdraw request approved. Amount added to your credit balance.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Agent withdraw approve failed', [
                'message' => $e->getMessage(),
                'withdraw_request_id' => $withdrawRequest->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function reject(Request $request, WithdrawRequest $withdrawRequest)
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $user = DB::transaction(function () use ($request, $withdrawRequest) {
                $withdrawRequest = WithdrawRequest::where('id', $withdrawRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $withdrawRequest || $withdrawRequest->status !== 'pending') {
                    throw new \RuntimeException('This withdraw request is already processed.');
                }

                $user = User::where('id', $withdrawRequest->user_id)
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $user ||
                    $user->role !== 'player' ||
                    (int) $user->agent_id !== (int) auth()->id()
                ) {
                    throw new \RuntimeException('You can only reject withdrawal requests from your own players.');
                }

                /*
                 * Withdrawal rejected.
                
                 */
                $user->increment('credit_balance', $withdrawRequest->amount);
                $user->refresh();

                $withdrawRequest->update([
                    'status' => 'rejected',
                    'admin_note' => $request->admin_note,
                    'rejected_at' => now(),
                ]);

                return $user;
            });

            try {
                broadcast(new CreditBalanceUpdated($user));
            } catch (\Throwable $broadcastError) {
                Log::error('Agent withdraw reject refund broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $user->id,
                ]);
            }

            return back()->with('success', 'Player withdraw request rejected and credit returned.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Agent withdraw reject failed', [
                'message' => $e->getMessage(),
                'withdraw_request_id' => $withdrawRequest->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}