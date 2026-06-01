<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Admin User Management
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage users, roles, credits, agent downlines, and transaction history.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-semibold">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">

        <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5">
            <form method="GET" action="{{ route('admin.users.index') }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-3">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search username, mobile, role..."
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <select
                    name="role"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Roles</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="agent" @selected(request('role') === 'agent')>Agent</option>
                    <option value="player" @selected(request('role') === 'player')>Player</option>
                    <option value="declare" @selected(request('role') === 'declare')>Declare</option>
                </select>

                <select
                    name="status"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="disapproved" @selected(request('status') === 'disapproved')>Deactivated</option>
                </select>

                <button class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm">
                    Filter
                </button>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($users as $user)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">

                    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 p-5">

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    type="button"
                                    onclick="openTransactionModal('transaction-modal-{{ $user->id }}')"
                                    class="text-left text-xl font-bold text-blue-700 hover:text-blue-900 hover:underline break-words"
                                >
                                    {{ $user->username }}
                                </button>

                                @if ($user->status === 'approved')
                                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                        Approved
                                    </span>
                                @elseif ($user->status === 'disapproved')
                                    <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                        Deactivated
                                    </span>
                                @else
                                    <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                        Pending
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 mt-2">
                                Click username to view transaction history.
                            </p>

                            <div class="mt-3 space-y-1 text-sm text-slate-600">
                                <p>
                                    <span class="font-semibold text-slate-900">Mobile:</span>
                                    {{ $user->mobile_number }}
                                </p>

                                <p>
                                    <span class="font-semibold text-slate-900">User ID:</span>
                                    {{ $user->id }}
                                </p>

                                <p>
                                    <span class="font-semibold text-slate-900">Agent:</span>
                                    {{ $user->agent?->username ?? 'None' }}
                                </p>

                                @if ($user->role === 'agent')
                                    <p>
                                        <span class="font-semibold text-slate-900">Downlines:</span>
                                        {{ $user->players_count }} players
                                    </p>
                                @endif

                                <p>
                                    <span class="font-semibold text-slate-900">Created:</span>
                                    {{ $user->created_at?->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <h3 class="text-sm font-bold text-slate-900">
                                Role
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Current:
                                <span class="font-bold capitalize text-slate-900">
                                    {{ $user->role }}
                                </span>
                            </p>

                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="mt-3 flex gap-2">
                                @csrf
                                @method('PATCH')

                                <select
                                    name="role"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="agent" @selected($user->role === 'agent')>Agent</option>
                                    <option value="player" @selected($user->role === 'player')>Player</option>
                                    <option value="declare" @selected($user->role === 'declare')>Declare</option>
                                </select>

                                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-semibold">
                                    Save
                                </button>
                            </form>
                        </div>

                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                            <h3 class="text-sm font-bold text-emerald-900">
                                Credit Balance
                            </h3>

                            <p class="text-2xl font-bold text-emerald-700 mt-2">
                                ₱<span id="admin-user-balance-{{ $user->id }}">
                                    {{ number_format($user->credit_balance ?? 0, 2) }}
                                </span>
                            </p>

                            @if ($user->role === 'agent')
                                <div class="mt-3 space-y-3">

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.giveCredit', $user) }}"
                                        class="flex flex-col sm:flex-row gap-2"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="number"
                                            name="credit_amount"
                                            step="0.01"
                                            min="1"
                                            placeholder="Amount to give"
                                            class="w-full rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                            required
                                        >

                                        <button class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 text-sm font-semibold whitespace-nowrap">
                                            Give Credit
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.users.getCredit', $user) }}"
                                        class="flex flex-col sm:flex-row gap-2"
                                        onsubmit="return confirm('Are you sure you want to get credit from this agent?')"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="number"
                                            name="credit_amount"
                                            step="0.01"
                                            min="1"
                                            placeholder="Amount to get"
                                            class="w-full rounded-lg border border-red-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                                            required
                                        >

                                        <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold whitespace-nowrap">
                                            Get Credit
                                        </button>
                                    </form>

                                </div>
                            @else
                                <p class="text-sm text-slate-500 mt-3">
                                    Credits can only be managed for agents.
                                </p>
                            @endif
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <h3 class="text-sm font-bold text-slate-900">
                                Actions
                            </h3>

                            <div class="mt-3 grid grid-cols-1 gap-2">
                                <button
                                    type="button"
                                    onclick="openUserModal('user-modal-{{ $user->id }}')"
                                    class="w-full rounded-lg bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 text-sm font-semibold"
                                >
                                    Edit User
                                </button>

                                @if ($user->status === 'approved')
                                    <form method="POST" action="{{ route('admin.users.disapprove', $user) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button class="w-full rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold">
                                            Deactivate
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button class="w-full rounded-lg bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-semibold">
                                            Approve
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.forceLogout', $user) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="w-full rounded-lg bg-slate-900 hover:bg-black text-white px-4 py-2 text-sm font-semibold">
                                        Force Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @if ($user->role === 'agent')
                        <div class="border-t border-slate-200 bg-slate-50 px-5 py-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-bold text-blue-600 hover:text-blue-800">
                                    View Downlines / Players
                                    <span class="text-slate-500">
                                        ({{ $user->players_count }})
                                    </span>
                                </summary>

                                <div class="mt-4">
                                    @if ($user->players->count() > 0)
                                        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
                                            <table class="w-full text-sm min-w-[800px]">
                                                <thead class="bg-slate-100 text-slate-700">
                                                    <tr>
                                                        <th class="p-3 text-left">Player ID</th>
                                                        <th class="p-3 text-left">Username</th>
                                                        <th class="p-3 text-left">Mobile</th>
                                                        <th class="p-3 text-left">Status</th>
                                                        <th class="p-3 text-left">Credit Balance</th>
                                                        <th class="p-3 text-left">Created</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($user->players as $player)
                                                        <tr class="border-t border-slate-200">
                                                            <td class="p-3">
                                                                {{ $player->id }}
                                                            </td>

                                                            <td class="p-3 font-semibold text-slate-900">
                                                                {{ $player->username }}
                                                            </td>

                                                            <td class="p-3">
                                                                {{ $player->mobile_number }}
                                                            </td>

                                                            <td class="p-3">
                                                                @if ($player->status === 'approved')
                                                                    <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                                                        Approved
                                                                    </span>
                                                                @elseif ($player->status === 'disapproved')
                                                                    <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                                                        Deactivated
                                                                    </span>
                                                                @else
                                                                    <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                                                        Pending
                                                                    </span>
                                                                @endif
                                                            </td>

                                                            <td class="p-3 font-bold text-emerald-700">
                                                                ₱{{ number_format($player->credit_balance ?? 0, 2) }}
                                                            </td>

                                                            <td class="p-3">
                                                                {{ $player->created_at?->format('M d, Y h:i A') }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-500">
                                            This agent has no downline players yet.
                                        </p>
                                    @endif
                                </div>
                            </details>
                        </div>
                    @endif

                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-6 py-10 text-center">
                    <h2 class="text-xl font-bold text-slate-900">
                        No users found
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Try changing your filters or search keyword.
                    </p>
                </div>
            @endforelse
        </section>

        @foreach ($users as $user)
            <div
                id="transaction-modal-{{ $user->id }}"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
            >
                <div class="w-full max-w-6xl rounded-2xl bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">
                                Transaction History
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $user->username }} — {{ ucfirst($user->role) }} —
                                Current Balance:
                                ₱{{ number_format($user->credit_balance ?? 0, 2) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closeTransactionModal('transaction-modal-{{ $user->id }}')"
                            class="rounded-lg bg-slate-100 hover:bg-slate-200 px-4 py-2 text-sm font-bold text-slate-700"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto">
                        @if ($user->creditTransactions->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="w-full min-w-[1000px] text-sm">
                                    <thead class="bg-slate-100 text-slate-700">
                                        <tr>
                                            <th class="p-3 text-left">Date</th>
                                            <th class="p-3 text-left">Type</th>
                                            <th class="p-3 text-left">Amount</th>
                                            <th class="p-3 text-left">Previous Balance</th>
                                            <th class="p-3 text-left">Current Balance</th>
                                            <th class="p-3 text-left">Details</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($user->creditTransactions as $transaction)
                                            <tr class="border-t border-slate-200">
                                                <td class="p-3 whitespace-nowrap">
                                                    {{ $transaction->created_at?->format('M d, Y h:i A') }}
                                                </td>

                                                <td class="p-3 whitespace-nowrap">
                                                    @if ($transaction->type === 'bet')
                                                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                                            Bet
                                                        </span>
                                                    @elseif ($transaction->type === 'withdraw')
                                                        <span class="rounded-full bg-orange-100 text-orange-700 px-3 py-1 text-xs font-bold">
                                                            Withdraw
                                                        </span>
                                                    @elseif ($transaction->type === 'agent_give_credit')
                                                        <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                                            Agent Gave Credit
                                                        </span>
                                                    @elseif ($transaction->type === 'agent_get_credit')
                                                        <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                                            Agent Got Credit
                                                        </span>
                                                    @elseif ($transaction->type === 'agent_transfer_out')
                                                        <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                                            Agent Transfer Out
                                                        </span>
                                                    @elseif ($transaction->type === 'agent_transfer_in')
                                                        <span class="rounded-full bg-cyan-100 text-cyan-700 px-3 py-1 text-xs font-bold">
                                                            Agent Transfer In
                                                        </span>
                                                    @elseif ($transaction->type === 'admin_give_credit')
                                                        <span class="rounded-full bg-purple-100 text-purple-700 px-3 py-1 text-xs font-bold">
                                                            Admin Gave Credit
                                                        </span>
                                                    @elseif ($transaction->type === 'admin_get_credit')
                                                        <span class="rounded-full bg-pink-100 text-pink-700 px-3 py-1 text-xs font-bold">
                                                            Admin Got Credit
                                                        </span>
                                                    @else
                                                        <span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-bold">
                                                            {{ ucwords(str_replace('_', ' ', $transaction->type)) }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td class="p-3 font-bold whitespace-nowrap">
                                                    ₱{{ number_format($transaction->amount, 2) }}
                                                </td>

                                                <td class="p-3 text-slate-600 whitespace-nowrap">
                                                    ₱{{ number_format($transaction->previous_balance, 2) }}
                                                </td>

                                                <td class="p-3 font-bold text-emerald-700 whitespace-nowrap">
                                                    ₱{{ number_format($transaction->current_balance, 2) }}
                                                </td>

                                                <td class="p-3 text-slate-600">
                                                    {{ $transaction->description }}

                                                    @if (!empty($transaction->meta))
                                                        <div class="mt-1 text-xs text-slate-500 space-x-1">
                                                            @if(isset($transaction->meta['bet_id']))
                                                                <span>Bet ID: {{ $transaction->meta['bet_id'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['side']))
                                                                <span>Side: {{ strtoupper($transaction->meta['side']) }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['odds']))
                                                                <span>Odds: {{ $transaction->meta['odds'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['withdraw_id']))
                                                                <span>Withdraw ID: {{ $transaction->meta['withdraw_id'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['withdraw_amount']))
                                                                <span>Withdraw Amount: ₱{{ number_format($transaction->meta['withdraw_amount'], 2) }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['payment_method']))
                                                                <span>Method: {{ $transaction->meta['payment_method'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['admin_username']))
                                                                <span>Admin: {{ $transaction->meta['admin_username'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['agent_username']))
                                                                <span>Agent: {{ $transaction->meta['agent_username'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['player_username']))
                                                                <span>Player: {{ $transaction->meta['player_username'] }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-xl bg-slate-50 p-8 text-center">
                                <h3 class="text-lg font-bold text-slate-900">
                                    No transaction history yet
                                </h3>

                                <p class="text-slate-500 mt-2">
                                    This user has no recorded credit transactions yet.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @foreach ($users as $user)
            <div
                id="user-modal-{{ $user->id }}"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
            >
                <div class="w-full max-w-2xl rounded-xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">
                                Edit User
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $user->username }} — {{ ucfirst($user->role) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closeUserModal('user-modal-{{ $user->id }}')"
                            class="rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-2 text-sm font-bold"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-slate-200 p-4">
                            <h3 class="text-sm font-bold text-slate-900">
                                Edit Information
                            </h3>

                            <form method="POST" action="{{ route('admin.users.info', $user) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Username
                                    </label>

                                    <input
                                        type="text"
                                        name="username"
                                        value="{{ $user->username }}"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        Mobile Number
                                    </label>

                                    <input
                                        type="text"
                                        name="mobile_number"
                                        value="{{ $user->mobile_number }}"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    >
                                </div>

                                <button class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold">
                                    Save Information
                                </button>
                            </form>
                        </div>

                        <div class="rounded-xl border border-slate-200 p-4">
                            <h3 class="text-sm font-bold text-slate-900">
                                Change Password
                            </h3>

                            <form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-4 space-y-3">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">
                                        New Password
                                    </label>

                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="Enter new password"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                        required
                                    >
                                </div>

                                <button class="w-full rounded-lg bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 text-sm font-semibold">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function openUserModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeUserModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        function openTransactionModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeTransactionModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('[id^="user-modal-"], [id^="transaction-modal-"]').forEach(function (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#16a34a'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Echo) {
                console.error('Laravel Echo is not loaded.');
                return;
            }

            function formatMoney(value) {
                return Number(value || 0).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            window.Echo.channel('admin.balances')
                .listen('.credit.updated', function (event) {
                    const balanceElement = document.getElementById('admin-user-balance-' + event.user_id);

                    if (balanceElement) {
                        balanceElement.textContent = formatMoney(event.credit_balance);
                    }

                    if (event.role === 'player' && typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Player Credit Updated',
                            text: event.username + ' new balance is ' + event.formatted_balance,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
        });
    </script>

</body>
</html>