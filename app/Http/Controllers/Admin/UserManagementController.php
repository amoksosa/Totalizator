<?php

namespace App\Http\Controllers\Admin;

use App\Events\CreditBalanceUpdated;
use App\Events\UserForceLoggedOut;
use App\Http\Controllers\Controller;
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
            ->with('agent')
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
        $request->validate([
            'credit_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($user->role !== 'agent') {
            return back()->withErrors([
                'credit_amount' => 'Admin can only give credits to agents.',
            ]);
        }

        $user->increment('credit_balance', $request->credit_amount);
        $user->refresh();

        try {
            broadcast(new CreditBalanceUpdated($user));
        } catch (\Throwable $broadcastError) {
            Log::error('Admin credit broadcast failed', [
                'message' => $broadcastError->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        return back()->with('success', 'Credit added to agent successfully.');
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