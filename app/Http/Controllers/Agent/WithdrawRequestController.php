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

        $withdrawRequest->load('user');

        if (
            ! $withdrawRequest->user ||
            $withdrawRequest->user->role !== 'player' ||
            (int) $withdrawRequest->user->agent_id !== (int) auth()->id()
        ) {
            abort(403, 'You can only approve withdrawal requests from your own players.');
        }

        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'This withdraw request is already processed.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $withdrawRequest->update([
            'status' => 'approved',
            'admin_note' => $request->admin_note,
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Player withdraw request approved successfully.');
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