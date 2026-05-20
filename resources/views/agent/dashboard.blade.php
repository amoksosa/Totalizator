<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-4 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Agent Dashboard
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage player codes, downline players, credit distribution, commissions, and withdrawals.
                </p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-2.5 text-sm font-semibold transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-10">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
            <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Welcome Agent, {{ auth()->user()->username }}!
                    </h2>

                    <p class="text-slate-600 mt-2">
                        Role:
                        <span class="font-semibold capitalize">
                            {{ auth()->user()->role }}
                        </span>
                    </p>
                </div>

                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5 min-w-full md:min-w-72">
                    <p class="text-sm text-emerald-700 font-semibold">
                        Agent Credit Balance
                    </p>

                    <h3 class="text-4xl font-bold text-emerald-800 mt-2">
                        ₱<span id="agent-balance">{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}</span>
                    </h3>

                    <p class="text-sm text-emerald-700 mt-2">
                        This balance can be distributed to your downline players or requested for withdrawal.
                    </p>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

                <a href="{{ route('agent.commissions.index') }}"
                   class="group block rounded-2xl bg-yellow-500 hover:bg-yellow-600 text-white p-6 transition shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                Commission Dashboard
                            </h3>

                            <p class="text-sm text-yellow-100 mt-2">
                                View your 5% commission from player bets.
                            </p>
                        </div>

                        <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold group-hover:bg-white/30 transition">
                            →
                        </div>
                    </div>
                </a>

                <a href="{{ route('agent.player-codes.index') }}"
                   class="group block rounded-2xl bg-blue-600 hover:bg-blue-700 text-white p-6 transition shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                Player Registration Codes
                            </h3>

                            <p class="text-sm text-blue-100 mt-2">
                                Generate registration codes for your players.
                            </p>
                        </div>

                        <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold group-hover:bg-white/30 transition">
                            →
                        </div>
                    </div>
                </a>

                <a href="{{ route('agent.users.index') }}"
                   class="group block rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white p-6 transition shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                User Management
                            </h3>

                            <p class="text-sm text-emerald-100 mt-2">
                                View your players and distribute credits.
                            </p>
                        </div>

                        <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold group-hover:bg-white/30 transition">
                            →
                        </div>
                    </div>
                </a>

                <a href="{{ route('withdrawals.index') }}"
                   class="group block rounded-2xl bg-cyan-600 hover:bg-cyan-700 text-white p-6 transition shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                Request My Withdrawal
                            </h3>

                            <p class="text-sm text-cyan-100 mt-2">
                                Request withdrawal from your own agent credit balance.
                            </p>
                        </div>

                        <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold group-hover:bg-white/30 transition">
                            →
                        </div>
                    </div>
                </a>

                <a href="{{ route('agent.withdrawals.index') }}"
                   class="group block rounded-2xl bg-purple-600 hover:bg-purple-700 text-white p-6 transition shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold">
                                Player Withdraw Requests
                            </h3>

                            <p class="text-sm text-purple-100 mt-2">
                                Approve or reject withdrawal requests from your players.
                            </p>
                        </div>

                        <div class="h-12 w-12 rounded-xl bg-white/20 flex items-center justify-center text-2xl font-bold group-hover:bg-white/30 transition">
                            →
                        </div>
                    </div>
                </a>

            </div>
        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userId = @json(auth()->id());
            const balanceElement = document.getElementById('agent-balance');

            function formatMoney(value) {
                return Number(value || 0).toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            if (!window.Echo) {
                console.error('Laravel Echo is not loaded. Check resources/js/bootstrap.js and npm run dev.');
                return;
            }

            window.Echo.channel('user.' + userId)
                .listen('.credit.updated', function (event) {
                    if (balanceElement) {
                        balanceElement.textContent = formatMoney(event.credit_balance);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Balance Updated',
                        text: 'Your new agent balance is ' + event.formatted_balance,
                        timer: 1600,
                        showConfirmButton: false
                    });
                })
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