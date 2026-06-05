<?php

namespace App\Http\Controllers\Admin;

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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $withdrawRequests = WithdrawRequest::query()
            ->with('user')
            ->where(function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->where('role', 'agent');
                })
                ->orWhereHas('user', function ($userQuery) {
                    $userQuery->where('role', 'player')
                        ->whereNull('agent_id');
                });
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

        return view('admin.withdrawals.index', compact('withdrawRequests'));
    }

    public function approve(Request $request, WithdrawRequest $withdrawRequest)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (! $this->adminCanProcess($withdrawRequest)) {
            abort(403, 'Admin can only approve agent withdrawals or players without an agent.');
        }

        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'This withdraw request is already processed.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $withdrawRequest) {
                $withdrawRequest = WithdrawRequest::where('id', $withdrawRequest->id)
                    ->lockForUpdate()
                    ->first();

                if (! $withdrawRequest || $withdrawRequest->status !== 'pending') {
                    throw new \RuntimeException('This withdraw request is already processed.');
                }

                if (! $this->adminCanProcess($withdrawRequest)) {
                    throw new \RuntimeException('Invalid admin withdrawal request.');
                }

                $withdrawRequest->update([
                    'status' => 'approved',
                    'admin_note' => $request->admin_note,
                    'approved_at' => now(),
                ]);
            });

            return back()->with('success', 'Withdraw request approved successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin withdraw approve failed', [
                'message' => $e->getMessage(),
                'withdraw_request_id' => $withdrawRequest->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function reject(Request $request, WithdrawRequest $withdrawRequest)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        if (! $this->adminCanProcess($withdrawRequest)) {
            abort(403, 'Admin can only reject agent withdrawals or players without an agent.');
        }

        if ($withdrawRequest->status !== 'pending') {
            return back()->with('error', 'This withdraw request is already processed.');
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

                if (! $this->adminCanProcess($withdrawRequest)) {
                    throw new \RuntimeException('Invalid admin withdrawal request.');
                }

                $user = User::where('id', $withdrawRequest->user_id)
                    ->lockForUpdate()
                    ->first();

                if (! $user) {
                    throw new \RuntimeException('User not found.');
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
                Log::error('Admin withdraw reject refund broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $user->id,
                ]);
            }

            return back()->with('success', 'Withdraw request rejected and credit returned.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin withdraw reject failed', [
                'message' => $e->getMessage(),
                'withdraw_request_id' => $withdrawRequest->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    private function adminCanProcess(WithdrawRequest $withdrawRequest): bool
    {
        $withdrawRequest->loadMissing('user');

        $user = $withdrawRequest->user;

        if (! $user) {
            return false;
        }

        if ($user->role === 'agent') {
            return true;
        }

        if ($user->role === 'player' && is_null($user->agent_id)) {
            return true;
        }

        return false;
    }
}