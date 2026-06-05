<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;

class PlayerCodeController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'agent') {
            abort(403);
        }

        $agent = User::find(auth()->id());

        if (! $agent->referral_code) {
            do {
                $code = 'AGT-' . strtoupper(Str::random(8));
            } while (User::where('referral_code', $code)->exists());

            $agent->update([
                'referral_code' => $code,
            ]);
        }

        return view('agent.player-codes');
    }
}