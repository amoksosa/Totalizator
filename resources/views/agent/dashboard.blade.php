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
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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

                <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Welcome Agent, {{ auth()->user()->username }}!
                    </h2>

                    <p class="text-slate-500 mt-1">
                        Role:
                        <span class="font-semibold text-slate-900 capitalize">
                            {{ auth()->user()->role }}
                        </span>
                    </p>
                </div>

                <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-5 py-3">
                    <p class="text-sm font-semibold text-emerald-700">
                        Agent Credit Balance
                    </p>

                    <p class="text-lg font-bold text-emerald-800">
                        ₱<span id="agent-balance">{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}</span>
                    </p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <a href="{{ route('agent.commissions.index') }}"
               class="group block bg-white border border-slate-200 hover:border-yellow-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-yellow-100 text-yellow-700 flex items-center justify-center text-2xl mb-4">
                            📊
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Commission Dashboard
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            View your commission from player bets and convert available commission to wallet.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-yellow-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('agent.player-codes.index') }}"
               class="group block bg-white border border-slate-200 hover:border-blue-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl mb-4">
                            🎟️
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Player Registration Codes
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Generate direct registration codes for your players.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-blue-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('agent.users.index') }}"
               class="group block bg-white border border-slate-200 hover:border-emerald-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mb-4">
                            👥
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            User Management
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            View your players, monitor downline accounts, and distribute credits.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-emerald-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('withdrawals.index') }}"
               class="group block bg-white border border-slate-200 hover:border-cyan-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-2xl mb-4">
                            💵
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Request My Withdrawal
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Request withdrawal from your own agent credit balance.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-cyan-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('agent.withdrawals.index') }}"
               class="group block bg-white border border-slate-200 hover:border-purple-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl mb-4">
                            💸
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Player Withdraw Requests
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Approve or reject withdrawal requests from your players.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-purple-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

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