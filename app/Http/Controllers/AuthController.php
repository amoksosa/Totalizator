<?php

namespace App\Http\Controllers;

use App\Models\AgentCode;
use App\Models\PlayerCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister(Request $request)
    {
        if ($request->filled('agent_code')) {
            session([
                'registration_type' => $request->input('type', 'player'),
                'registration_agent_code' => $request->agent_code,
            ]);
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],

            'type' => ['nullable', 'string'],
            'agent_code' => ['nullable', 'string'],
            'admin_agent_code' => ['nullable', 'string'],
            'player_code' => ['nullable', 'string'],
        ]);

        $type = $request->input('type', session('registration_type', 'player'));
        $agentCodeFromLink = $request->input('agent_code') ?: session('registration_agent_code');

        if ($request->filled('admin_agent_code') && $agentCodeFromLink) {
            return back()
                ->withErrors(['admin_agent_code' => 'Use only one registration method.'])
                ->withInput();
        }

        if ($request->filled('admin_agent_code') && $request->filled('player_code')) {
            return back()
                ->withErrors(['admin_agent_code' => 'Use only one registration method.'])
                ->withInput();
        }

        if ($agentCodeFromLink && $request->filled('player_code')) {
            return back()
                ->withErrors(['agent_code' => 'Use only one registration method.'])
                ->withInput();
        }

        $role = 'player';
        $status = 'pending';
        $agentId = null;
        $referralCode = null;

        $agentCodeRecord = null;
        $playerCodeRecord = null;
        $agentFromLink = null;

        /*
        
        | Admin Agent Registration Code
        

        */

        if ($request->filled('admin_agent_code')) {
            $agentCodeRecord = AgentCode::where('code', strtoupper($request->admin_agent_code))
                ->where('is_used', false)
                ->first();

            if (! $agentCodeRecord) {
                return back()
                    ->withErrors([
                        'admin_agent_code' => 'Invalid or already used agent code.',
                    ])
                    ->withInput();
            }

            $role = 'agent';
            $status = 'approved';
            $agentId = null;
            $referralCode = $this->generateUniqueAgentReferralCode();
        }

        /*
        
        | Agent Player Registration Link
        
        
        */

        if ($type === 'player' && $agentCodeFromLink) {
            $agentFromLink = User::where('role', 'agent')
                ->where('referral_code', $agentCodeFromLink)
                ->first();

            if (! $agentFromLink) {
                return back()
                    ->withErrors([
                        'agent_code' => 'Invalid agent registration link.',
                    ])
                    ->withInput();
            }

            $role = 'player';
            $status = 'pending';
            $agentId = $agentFromLink->id;
            $referralCode = null;
        }

        /*
        
        | Old Player Code System Fallback
        
        */

        if ($request->filled('player_code')) {
            $playerCodeRecord = PlayerCode::where('code', strtoupper($request->player_code))
                ->where('is_used', false)
                ->first();

            if (! $playerCodeRecord) {
                return back()
                    ->withErrors([
                        'player_code' => 'Invalid or already used player code.',
                    ])
                    ->withInput();
            }

            $role = 'player';
            $status = 'pending';
            $agentId = $playerCodeRecord->created_by;
            $referralCode = null;
        }

        $user = User::create([
            'mobile_number' => $request->mobile_number,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $role,
            'status' => $status,
            'agent_id' => $agentId,
            'referral_code' => $referralCode,
            'credit_balance' => 0,
        ]);

        session()->forget([
            'registration_type',
            'registration_agent_code',
        ]);

        if ($agentCodeRecord) {
            $agentCodeRecord->update([
                'used_by' => $user->id,
                'is_used' => true,
                'used_at' => now(),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Agent account registered successfully. You can now login.');
        }

        if ($playerCodeRecord) {
            $playerCodeRecord->update([
                'used_by' => $user->id,
                'is_used' => true,
                'used_at' => now(),
            ]);

            return redirect()
                ->route('login')
                ->with('success', 'Player account registered successfully. Please wait for admin approval.');
        }

        if ($agentFromLink) {
            return redirect()
                ->route('login')
                ->with('success', 'Player account registered under agent successfully. Please wait for admin approval.');
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

    private function generateUniqueAgentReferralCode(): string
    {
        do {
            $code = 'AGT-' . strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}