<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Sales Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <nav class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">
                Daily Sales Report
            </h1>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg font-semibold">
                    Back
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-10">

        <form method="GET" action="{{ route('admin.commissions.index') }}" class="mb-6 bg-white rounded-2xl shadow p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                    class="w-full border border-slate-300 rounded-lg px-4 py-2"
                >

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search agent or player..."
                    class="w-full border border-slate-300 rounded-lg px-4 py-2"
                >

                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold">
                    Filter
                </button>

                <a href="{{ route('admin.commissions.index') }}"
                   class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-5 py-2 rounded-lg font-semibold text-center">
                    Reset
                </a>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Total Bet Amount
                </p>

                <h2 class="text-2xl font-bold text-slate-800 mt-2">
                    ₱{{ number_format($totalBetAmount, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Agent Commission 3%
                </p>

                <h2 class="text-2xl font-bold text-emerald-600 mt-2">
                    ₱{{ number_format($totalAgentCommission, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Company Commission 2%
                </p>

                <h2 class="text-2xl font-bold text-blue-600 mt-2">
                    ₱{{ number_format($totalCompanyCommission, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Total Commission 5%
                </p>

                <h2 class="text-2xl font-bold text-red-600 mt-2">
                    ₱{{ number_format($totalCommission, 2) }}
                </h2>
            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Agent Approved Withdraw
                </p>

                <h2 class="text-2xl font-bold text-red-600 mt-2">
                    ₱{{ number_format($totalAgentApprovedWithdraw, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Agent Pending Withdraw
                </p>

                <h2 class="text-2xl font-bold text-yellow-600 mt-2">
                    ₱{{ number_format($totalAgentPendingWithdraw, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Agent Rejected Withdraw
                </p>

                <h2 class="text-2xl font-bold text-slate-700 mt-2">
                    ₱{{ number_format($totalAgentRejectedWithdraw, 2) }}
                </h2>
            </div>

        </div>

        {{-- Wallet Summary Records --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Total Wallet of Players
                </p>

                <h2 class="text-2xl font-bold text-slate-800 mt-2">
                    ₱{{ number_format($totalPlayerWallet, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Total Wallet of Agents
                </p>

                <h2 class="text-2xl font-bold text-purple-600 mt-2">
                    ₱{{ number_format($totalAgentWallet, 2) }}
                </h2>
            </div>

            <div class="bg-white rounded-2xl shadow p-6">
                <p class="text-sm text-slate-500">
                    Total Wallet of Agents + Players
                </p>

                <h2 class="text-2xl font-bold text-orange-600 mt-2">
                    ₱{{ number_format($totalAgentAndPlayerWallet, 2) }}
                </h2>
            </div>

        </div>

        <section class="bg-white rounded-2xl shadow overflow-hidden mb-8">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">
                    Commission Records
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Agent commission, company commission, and total commission from player bets.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-slate-200 text-slate-700">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Agent</th>
                            <th class="p-3 text-left">Player</th>
                            <th class="p-3 text-left">Bet Amount</th>
                            <th class="p-3 text-left">Agent 3%</th>
                            <th class="p-3 text-left">Company 2%</th>
                            <th class="p-3 text-left">Total 5%</th>
                            <th class="p-3 text-left">Side</th>
                            <th class="p-3 text-left">Odds</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($commissions as $commission)
                            <tr class="border-t border-slate-200">
                                <td class="p-3">
                                    {{ $commission->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="p-3">
                                    {{ $commission->agent?->username ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    {{ $commission->player?->username ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    ₱{{ number_format($commission->bet_amount, 2) }}
                                </td>

                                <td class="p-3 font-semibold text-emerald-600">
                                    ₱{{ number_format($commission->commission_amount, 2) }}
                                </td>

                                <td class="p-3 font-semibold text-blue-600">
                                    ₱{{ number_format($commission->company_commission_amount, 2) }}
                                </td>

                                <td class="p-3 font-semibold text-red-600">
                                    ₱{{ number_format($commission->total_commission_amount, 2) }}
                                </td>

                                <td class="p-3">
                                    {{ strtoupper($commission->side) }}
                                </td>

                                <td class="p-3">
                                    {{ $commission->odds }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-6 text-center text-slate-500">
                                    No commission records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5">
                {{ $commissions->links() }}
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-xl font-bold text-slate-800">
                    Agent Withdraw Report
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Only agent withdrawal requests are shown here. Player withdrawals are not included.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[900px]">
                    <thead class="bg-slate-200 text-slate-700">
                        <tr>
                            <th class="p-3 text-left">Date</th>
                            <th class="p-3 text-left">Agent</th>
                            <th class="p-3 text-left">Amount</th>
                            <th class="p-3 text-left">Status</th>
                            <th class="p-3 text-left">Payment Method</th>
                            <th class="p-3 text-left">Account Name</th>
                            <th class="p-3 text-left">Account Number</th>
                            <th class="p-3 text-left">Note</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($agentWithdrawRequests as $withdraw)
                            <tr class="border-t border-slate-200">
                                <td class="p-3">
                                    {{ $withdraw->created_at?->format('M d, Y h:i A') }}
                                </td>

                                <td class="p-3 font-semibold">
                                    {{ $withdraw->user?->username ?? 'Unknown Agent' }}
                                </td>

                                <td class="p-3 font-bold text-red-600">
                                    ₱{{ number_format($withdraw->amount, 2) }}
                                </td>

                                <td class="p-3">
                                    @if ($withdraw->status === 'approved')
                                        <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            Approved
                                        </span>
                                    @elseif ($withdraw->status === 'rejected')
                                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            Rejected
                                        </span>
                                    @else
                                        <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="p-3">
                                    {{ $withdraw->payment_method ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    {{ $withdraw->account_name ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    {{ $withdraw->account_number ?? 'N/A' }}
                                </td>

                                <td class="p-3">
                                    {{ $withdraw->note ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-6 text-center text-slate-500">
                                    No agent withdraw requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5">
                {{ $agentWithdrawRequests->links() }}
            </div>
        </section>

    </main>

</body>
</html>