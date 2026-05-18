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
                    Manage players registered under your player codes and distribute credits.
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
                    ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                </h2>

                <p class="text-base text-emerald-700 mt-3">
                    You can only distribute credits up to your available balance.
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
                                <h2 class="text-2xl font-extrabold text-slate-950 break-words">
                                    {{ $player->username }}
                                </h2>

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

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Give Credit
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                This will deduct from your agent balance.
                            </p>

                            <form method="POST"
                                  action="{{ route('agent.users.giveCredit', $player) }}"
                                  class="mt-4 flex flex-col sm:flex-row gap-2">
                                @csrf
                                @method('PATCH')

                                <input
                                    type="number"
                                    name="credit_amount"
                                    step="0.01"
                                    min="1"
                                    placeholder="Amount"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                    required
                                >

                                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-sm font-bold transition whitespace-nowrap">
                                    Give Credit
                                </button>
                            </form>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Account Info
                            </h3>

                            <div class="mt-4 space-y-2 text-base text-slate-600">
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
                const balanceElement = document.getElementById('player-balance-' + event.user_id);

                if (balanceElement) {
                    balanceElement.textContent = formatMoney(event.credit_balance);
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Player Balance Updated',
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