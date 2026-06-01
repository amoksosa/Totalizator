<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent User Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="w-full max-w-[1500px] mx-auto px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Agent User Management
                </h1>

                <p class="text-base text-slate-500 mt-1">
                    Manage players registered under your player codes and distribute or get credits.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.dashboard') }}"
                   class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-[1500px] mx-auto px-6 py-8">

        <section class="grid grid-cols-1 lg:grid-cols-[1fr_1.5fr] gap-5 mb-6">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6">
                <p class="text-base text-emerald-700 font-bold">
                    Your Agent Credit Balance
                </p>

                <h2 class="text-5xl font-extrabold text-emerald-800 mt-3">
                    ₱<span id="agent-balance-{{ auth()->id() }}">
                        {{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                    </span>
                </h2>

                <p class="text-base text-emerald-700 mt-3">
                    Give credit to players or get credit back from them.
                </p>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 p-6">
                <p class="text-base text-slate-500 font-bold">
                    Quick Actions
                </p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('agent.player-codes.index') }}"
                       class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-center text-sm font-bold transition">
                        Player Registration Codes
                    </a>

                    <a href="{{ route('agent.dashboard') }}"
                       class="rounded-xl bg-slate-700 hover:bg-slate-800 text-white px-5 py-3 text-center text-sm font-bold transition">
                        Back to Dashboard
                    </a>
                </div>

                <p class="text-sm text-slate-500 mt-4">
                    Players listed here are only the players registered under your player codes.
                </p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <form method="GET" action="{{ route('agent.users.index') }}"
                  class="grid grid-cols-1 md:grid-cols-[1fr_auto] gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search username, mobile, status..."
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 transition">
                    Search
                </button>
            </form>
        </section>

        <section class="space-y-5">
            @forelse ($players as $player)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-[1.4fr_1fr_1.2fr_1.2fr] gap-5 p-6 items-start">

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    onclick="openTransactionModal('transaction-modal-{{ $player->id }}')"
                                    class="text-left text-2xl font-extrabold text-blue-700 hover:text-blue-900 hover:underline break-words"
                                >
                                    {{ $player->username }}
                                </button>

                                @if ($player->status === 'approved')
                                    <span class="inline-flex rounded-full bg-green-100 text-green-700 px-4 py-1.5 text-sm font-extrabold">
                                        Approved
                                    </span>
                                @elseif ($player->status === 'disapproved')
                                    <span class="inline-flex rounded-full bg-red-100 text-red-700 px-4 py-1.5 text-sm font-extrabold">
                                        Disapproved
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 text-yellow-700 px-4 py-1.5 text-sm font-extrabold">
                                        Pending
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500 mt-2">
                                Click username to view transaction history.
                            </p>

                            <div class="mt-4 space-y-1 text-base text-slate-600">
                                <p>
                                    <span class="font-bold text-slate-900">Mobile:</span>
                                    {{ $player->mobile_number }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Player ID:</span>
                                    {{ $player->id }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Registered:</span>
                                    {{ $player->created_at?->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
                            <h3 class="text-lg font-extrabold text-emerald-900">
                                Player Balance
                            </h3>

                            <p class="text-4xl font-extrabold text-emerald-800 mt-3">
                                ₱<span id="player-balance-{{ $player->id }}">
                                    {{ number_format($player->credit_balance ?? 0, 2) }}
                                </span>
                            </p>

                            <p class="text-sm text-emerald-700 mt-3">
                                Current available game credit.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-emerald-200 p-5">
                            <h3 class="text-lg font-extrabold text-emerald-900">
                                Give Credit
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                This will deduct from your agent balance and add to the player.
                            </p>

                            <form method="POST"
                                  action="{{ route('agent.users.giveCredit', $player) }}"
                                  class="mt-4 flex flex-col gap-2">
                                @csrf
                                @method('PATCH')

                                <input
                                    type="number"
                                    name="credit_amount"
                                    step="0.01"
                                    min="1"
                                    placeholder="Amount to give"
                                    class="w-full rounded-xl border border-emerald-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    required
                                >

                                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-sm font-bold transition whitespace-nowrap">
                                    Give Credit
                                </button>
                            </form>
                        </div>

                        <div class="rounded-2xl border border-red-200 p-5">
                            <h3 class="text-lg font-extrabold text-red-900">
                                Get Credit
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                This will deduct from the player and return credit to your agent balance.
                            </p>

                            <form method="POST"
                                  action="{{ route('agent.users.getCredit', $player) }}"
                                  class="mt-4 flex flex-col gap-2"
                                  onsubmit="return confirm('Are you sure you want to get credit from this player?')">
                                @csrf
                                @method('PATCH')

                                <input
                                    type="number"
                                    name="credit_amount"
                                    step="0.01"
                                    min="1"
                                    placeholder="Amount to get"
                                    class="w-full rounded-xl border border-red-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-red-500"
                                    required
                                >

                                <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition whitespace-nowrap">
                                    Get Credit
                                </button>
                            </form>
                        </div>

                    </div>

                    <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm text-slate-600">
                            <p>
                                <span class="font-bold text-slate-900">Role:</span>
                                {{ ucfirst($player->role) }}
                            </p>

                            <p>
                                <span class="font-bold text-slate-900">Status:</span>
                                {{ ucfirst($player->status) }}
                            </p>

                            <p>
                                <span class="font-bold text-slate-900">Agent:</span>
                                {{ auth()->user()->username }}
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-12 text-center">
                    <h2 class="text-2xl font-bold text-slate-900">
                        No players found
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Generate a player registration code and let your player register using that code.
                    </p>

                    <a href="{{ route('agent.player-codes.index') }}"
                       class="inline-block mt-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 text-sm font-bold transition">
                        Generate Player Code
                    </a>
                </div>
            @endforelse
        </section>

        @foreach ($players as $player)
            <div
                id="transaction-modal-{{ $player->id }}"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
            >
                <div class="w-full max-w-5xl rounded-2xl bg-white shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-900">
                                Transaction History
                            </h2>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $player->username }} — Current Balance:
                                ₱{{ number_format($player->credit_balance ?? 0, 2) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closeTransactionModal('transaction-modal-{{ $player->id }}')"
                            class="rounded-lg bg-slate-100 hover:bg-slate-200 px-4 py-2 text-sm font-bold text-slate-700"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto">
                        @if ($player->creditTransactions->count() > 0)
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="w-full min-w-[900px] text-sm">
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
                                        @foreach ($player->creditTransactions as $transaction)
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
                                                        <div class="mt-1 text-xs text-slate-500">
                                                            @if(isset($transaction->meta['bet_id']))
                                                                <span>Bet ID: {{ $transaction->meta['bet_id'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['side']))
                                                                <span class="ml-1">Side: {{ strtoupper($transaction->meta['side']) }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['odds']))
                                                                <span class="ml-1">Odds: {{ $transaction->meta['odds'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['withdraw_amount']))
                                                                <span>Withdraw Amount: ₱{{ number_format($transaction->meta['withdraw_amount'], 2) }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['agent_username']))
                                                                <span>Agent: {{ $transaction->meta['agent_username'] }}</span>
                                                            @endif

                                                            @if(isset($transaction->meta['player_username']))
                                                                <span class="ml-1">Player: {{ $transaction->meta['player_username'] }}</span>
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
                                    This player has no recorded credit transactions yet.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-6">
            {{ $players->links() }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                document.querySelectorAll('[id^="transaction-modal-"]').forEach(function (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const agentId = @json(auth()->id());

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

            window.Echo.channel('agent.' + agentId + '.balances')
                .listen('.credit.updated', function (event) {
                    const playerBalanceElement = document.getElementById('player-balance-' + event.user_id);
                    const agentBalanceElement = document.getElementById('agent-balance-' + event.user_id);

                    if (playerBalanceElement) {
                        playerBalanceElement.textContent = formatMoney(event.credit_balance);
                    }

                    if (agentBalanceElement) {
                        agentBalanceElement.textContent = formatMoney(event.credit_balance);
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Credit Balance Updated',
                            text: event.username + ' new balance is ' + event.formatted_balance,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userId = @json(auth()->id());

            if (!window.Echo) {
                console.error('Laravel Echo is not loaded.');
                return;
            }

            window.Echo.channel('user.' + userId)
                .listen('.force.logout', function (event) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Logged Out',
                        text: event.message,
                        confirmButtonColor: '#dc2626',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        window.location.href = event.redirect_url;
                    });

                    setTimeout(function () {
                        window.location.href = event.redirect_url;
                    }, 2500);
                });
        });
    </script>

</body>
</html>