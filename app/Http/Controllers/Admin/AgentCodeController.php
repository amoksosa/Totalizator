<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentCode;
use Illuminate\Support\Str;

class AgentCodeController extends Controller
{
    public function index()
    {
        $codes = AgentCode::with(['creator', 'usedBy'])
            ->latest()
            ->paginate(10);

        return view('admin.agent-codes.index', compact('codes'));
    }

    public function store()
    {
        do {
            $code = 'AGENT-' . strtoupper(Str::random(8));
        } while (AgentCode::where('code', $code)->exists());

        AgentCode::create([
            'code' => $code,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Agent registration code created successfully.');
    }
}