<?php

namespace App\Http\Controllers\Admin;

use App\Events\CreditBalanceUpdated;
use App\Events\UserForceLoggedOut;
use App\Http\Controllers\Controller;
use App\Models\CreditTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $users = User::query()
            ->with([
                'agent',
                'players',
                'creditTransactions' => function ($query) {
                    $query->latest()->limit(50);
                },
            ])
            ->withCount('players')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%")
                        ->orWhere('role', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function approve(User $user)
    {
        $user->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'User approved successfully.');
    }

    public function disapprove(User $user)
    {
        $user->update([
            'status' => 'disapproved',
        ]);

        $this->forceLogoutUser($user);
        $this->broadcastForceLogout($user);

        return back()->with('success', 'User deactivated successfully.');
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', Rule::in(['admin', 'agent', 'player', 'declare'])],
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return back()->with('success', 'User role updated successfully.');
    }

    public function updateInfo(Request $request, User $user)
    {
        $request->validate([
            'mobile_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('users', 'mobile_number')->ignore($user->id),
            ],
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
        ]);

        $user->update([
            'mobile_number' => $request->mobile_number,
            'username' => $request->username,
        ]);

        return back()->with('success', 'User information updated successfully.');
    }

    public function changePassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $this->forceLogoutUser($user);
        $this->broadcastForceLogout($user);

        return back()->with('success', 'Password changed successfully. User has been logged out.');
    }

    public function giveCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($user->role !== 'agent') {
            return back()->withErrors([
                'credit_amount' => 'Admin can only give credits to agents.',
            ]);
        }

        $amount = (float) $request->credit_amount;

        try {
            $updatedUser = DB::transaction(function () use ($user, $amount) {
                $agent = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $agent) {
                    throw new \RuntimeException('Agent not found.');
                }

                $previousBalance = (float) $agent->credit_balance;

                $agent->increment('credit_balance', $amount);
                $agent->refresh();

                CreditTransaction::create([
                    'user_id' => $agent->id,
                    'agent_id' => $agent->id,
                    'type' => 'admin_give_credit',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $agent->credit_balance,
                    'description' => 'Admin gave credit to agent.',
                    'meta' => [
                        'admin_id' => auth()->id(),
                        'admin_username' => auth()->user()->username,
                        'agent_id' => $agent->id,
                        'agent_username' => $agent->username,
                    ],
                ]);

                return $agent;
            });

            try {
                broadcast(new CreditBalanceUpdated($updatedUser));
            } catch (\Throwable $broadcastError) {
                Log::error('Admin credit broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $updatedUser->id,
                ]);
            }

            return back()->with('success', 'Credit added to agent successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin give credit failed', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function getCredit(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($user->role !== 'agent') {
            return back()->withErrors([
                'credit_amount' => 'Admin can only get credits from agents.',
            ]);
        }

        $amount = (float) $request->credit_amount;

        try {
            $updatedUser = DB::transaction(function () use ($user, $amount) {
                $agent = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $agent) {
                    throw new \RuntimeException('Agent not found.');
                }

                if ((float) $agent->credit_balance < $amount) {
                    throw new \RuntimeException('Agent does not have enough credit balance.');
                }

                $previousBalance = (float) $agent->credit_balance;

                $agent->decrement('credit_balance', $amount);
                $agent->refresh();

                CreditTransaction::create([
                    'user_id' => $agent->id,
                    'agent_id' => $agent->id,
                    'type' => 'admin_get_credit',
                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $agent->credit_balance,
                    'description' => 'Admin got credit from agent.',
                    'meta' => [
                        'admin_id' => auth()->id(),
                        'admin_username' => auth()->user()->username,
                        'agent_id' => $agent->id,
                        'agent_username' => $agent->username,
                    ],
                ]);

                return $agent;
            });

            try {
                broadcast(new CreditBalanceUpdated($updatedUser));
            } catch (\Throwable $broadcastError) {
                Log::error('Admin get credit broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $updatedUser->id,
                ]);
            }

            return back()->with('success', 'Credit taken from agent successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Admin get credit failed', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    public function forceLogout(User $user)
    {
        $this->forceLogoutUser($user);
        $this->broadcastForceLogout($user);

        return back()->with('success', 'User forced logout successfully.');
    }

    private function forceLogoutUser(User $user)
    {
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();
    }

    private function broadcastForceLogout(User $user)
    {
        try {
            broadcast(new UserForceLoggedOut($user));
        } catch (\Throwable $broadcastError) {
            Log::error('Force logout broadcast failed', [
                'message' => $broadcastError->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}