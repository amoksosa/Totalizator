<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\PlayerCode;
use Illuminate\Support\Str;

class PlayerCodeController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'agent' && auth()->user()->role !== 'admin') {
            abort(403);
        }

        $codes = PlayerCode::with(['creator', 'usedBy'])
            ->where('created_by', auth()->id())
            ->latest()
            ->paginate(10);

        return view('agent.player-codes.index', compact('codes'));
    }

    public function store()
    {
        if (auth()->user()->role !== 'agent' && auth()->user()->role !== 'admin') {
            abort(403);
        }

        do {
            $code = 'PLAYER-' . strtoupper(Str::random(8));
        } while (PlayerCode::where('code', $code)->exists());

        PlayerCode::create([
            'code' => $code,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Player registration code created successfully.');
    }
}