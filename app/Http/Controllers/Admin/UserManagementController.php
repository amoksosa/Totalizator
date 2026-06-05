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
use Illuminate\Support\Str;
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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user->update([
            'status' => 'approved',
        ]);

        return back()->with('success', 'User approved successfully.');
    }

    public function disapprove(User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $user->update([
            'status' => 'disapproved',
        ]);

        $this->forceLogoutUser($user);
        $this->broadcastForceLogout($user);

        return back()->with('success', 'User deactivated successfully.');
    }

    public function updateRole(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'agent', 'player', 'declare'])],
        ]);

        $newRole = $validated['role'];

        $updateData = [
            'role' => $newRole,
        ];

        

        if ($newRole === 'agent' && ! $user->referral_code) {
            $updateData['referral_code'] = $this->generateUniqueAgentReferralCode();
        }

        

        if (in_array($newRole, ['admin', 'agent', 'declare'])) {
            $updateData['agent_id'] = null;
        }

        

        if ($newRole === 'agent') {
            $updateData['status'] = 'approved';
        }

        $user->update($updateData);

        return back()->with('success', 'User role updated successfully.');
    }

    public function updateInfo(Request $request, User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

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

        if (! in_array($user->role, ['agent', 'player'])) {
            return back()->withErrors([
                'credit_amount' => 'Admin can only give credits to agents or players.',
            ]);
        }

        $amount = round((float) $request->credit_amount, 2);

        try {
            $updatedUser = DB::transaction(function () use ($user, $amount) {
                $targetUser = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $targetUser) {
                    throw new \RuntimeException('User not found.');
                }

                if (! in_array($targetUser->role, ['agent', 'player'])) {
                    throw new \RuntimeException('Admin can only give credits to agents or players.');
                }

                $previousBalance = (float) $targetUser->credit_balance;

                $targetUser->increment('credit_balance', $amount);
                $targetUser->refresh();

                CreditTransaction::create([
                    'user_id' => $targetUser->id,
                    'agent_id' => $targetUser->role === 'agent'
                        ? $targetUser->id
                        : $targetUser->agent_id,

                    'type' => $targetUser->role === 'agent'
                        ? 'admin_give_credit'
                        : 'admin_give_player_credit',

                    'amount' => $amount,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $targetUser->credit_balance,

                    'description' => $targetUser->role === 'agent'
                        ? 'Admin gave credit to agent.'
                        : 'Admin gave credit to player.',

                    'meta' => [
                        'admin_id' => auth()->id(),
                        'admin_username' => auth()->user()->username,
                        'target_user_id' => $targetUser->id,
                        'target_username' => $targetUser->username,
                        'target_role' => $targetUser->role,
                        'agent_id' => $targetUser->agent_id,
                    ],
                ]);

                return $targetUser;
            });

            try {
                broadcast(new CreditBalanceUpdated($updatedUser));
            } catch (\Throwable $broadcastError) {
                Log::error('Admin credit broadcast failed', [
                    'message' => $broadcastError->getMessage(),
                    'user_id' => $updatedUser->id,
                ]);
            }

            return back()->with('success', 'Credit added successfully.');
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

        $amount = round((float) $request->credit_amount, 2);

        try {
            $updatedUser = DB::transaction(function () use ($user, $amount) {
                $agent = User::where('id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $agent) {
                    throw new \RuntimeException('Agent not found.');
                }

                if ($agent->role !== 'agent') {
                    throw new \RuntimeException('Admin can only get credits from agents.');
                }

                if ((float) $agent->credit_balance < $amount) {
                    throw new \RuntimeException('Agent has insufficient credit balance.');
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
                    'description' => 'Admin retrieved credit from agent.',
                    'meta' => [
                        'admin_id' => auth()->id(),
                        'admin_username' => auth()->user()->username,
                        'target_user_id' => $agent->id,
                        'target_username' => $agent->username,
                        'target_role' => $agent->role,
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

            return back()->with('success', 'Credit retrieved successfully.');
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
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $this->forceLogoutUser($user);
        $this->broadcastForceLogout($user);

        return back()->with('success', 'User has been force logged out.');
    }

    private function generateUniqueAgentReferralCode(): string
    {
        do {
            $code = 'AGT-' . strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    private function forceLogoutUser(User $user): void
    {
        try {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        } catch (\Throwable $e) {
            Log::warning('Force logout session delete failed', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();
    }

    private function broadcastForceLogout(User $user): void
    {
        try {
            broadcast(new UserForceLoggedOut($user));
        } catch (\Throwable $broadcastError) {
            Log::error('User force logout broadcast failed', [
                'message' => $broadcastError->getMessage(),
                'user_id' => $user->id,
            ]);
        }
    }
}