<?php

namespace App\Http\Controllers;

use App\Models\AgentCode;
use App\Models\PlayerCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'agent_code' => ['nullable', 'string'],
            'player_code' => ['nullable', 'string'],
        ]);

        if ($request->filled('agent_code') && $request->filled('player_code')) {
            return back()
                ->withErrors([
                    'agent_code' => 'Use only one registration code.',
                ])
                ->withInput();
        }

        $role = 'player';
        $status = 'pending';
        $agentId = null;

        $agentCode = null;
        $playerCode = null;

        if ($request->filled('agent_code')) {
            $agentCode = AgentCode::where('code', strtoupper($request->agent_code))
                ->where('is_used', false)
                ->first();

            if (! $agentCode) {
                return back()
                    ->withErrors([
                        'agent_code' => 'Invalid or already used agent code.',
                    ])
                    ->withInput();
            }

            $role = 'agent';
            $status = 'approved';
        }

        if ($request->filled('player_code')) {
            $playerCode = PlayerCode::where('code', strtoupper($request->player_code))
                ->where('is_used', false)
                ->first();

            if (! $playerCode) {
                return back()
                    ->withErrors([
                        'player_code' => 'Invalid or already used player code.',
                    ])
                    ->withInput();
            }

            $role = 'player';
            $status = 'pending';
            $agentId = $playerCode->created_by;
        }

        $user = User::create([
            'mobile_number' => $request->mobile_number,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $role,
            'status' => $status,
            'agent_id' => $agentId,
            'credit_balance' => 0,
        ]);

        if ($agentCode) {
            $agentCode->update([
                'used_by' => $user->id,
                'is_used' => true,
                'used_at' => now(),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Agent account registered successfully. You can now login.');
        }

        if ($playerCode) {
            $playerCode->update([
                'used_by' => $user->id,
                'is_used' => true,
                'used_at' => now(),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Player account registered successfully. Please wait for admin approval.');
        }

        return redirect()
            ->route('login')
            ->with('success', 'Registration successful. Please wait for admin approval.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors([
                    'username' => 'Invalid username or password.',
                ])
                ->onlyInput('username');
        }

        if ($user->status !== 'approved') {
            return back()
                ->withErrors([
                    'username' => 'Your account is not approved yet.',
                ])
                ->onlyInput('username');
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->redirectByRole($user);
    }

    public function adminDashboard()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.dashboard');
    }

    public function agentDashboard()
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        return view('agent.dashboard');
    }

    public function playerDashboard()
    {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        return view('player.dashboard');
    }

    public function declareDashboard()
    {
        if (auth()->user()->role !== 'declare') {
            abort(403);
        }

        return view('declare.dashboard');
    }

    private function redirectByRole($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'agent') {
            return redirect()->route('agent.dashboard');
        }

        if ($user->role === 'declare') {
            return redirect()->route('declare.dashboard');
        }

        return redirect()->route('player.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}