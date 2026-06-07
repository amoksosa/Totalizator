<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        {{-- Header --}}
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-600 to-teal-700 text-xl text-white shadow">
                            🧾
                        </div>

                        <div>
                            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                                Agent Dashboard
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Manage player codes, downline users, credit distribution, commissions, and withdrawals.
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">

            {{-- Welcome / Hero --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-900"></div>

                    <div class="relative grid grid-cols-1 gap-6 px-6 py-8 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-emerald-200">
                                Welcome back
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-white">
                                {{ auth()->user()->username }}
                            </h2>

                            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-300">
                                You are logged in as
                                <span class="font-bold capitalize text-white">
                                    {{ auth()->user()->role }}
                                </span>.
                                Use the modules below to manage your players and wallet operations.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 px-6 py-5 backdrop-blur">
                            <p class="text-sm font-bold text-slate-300">
                                Agent Credit Balance
                            </p>

                            <p class="mt-2 text-3xl font-black text-white">
                                ₱<span id="agent-balance">{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Quick Overview --}}
            <section class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Account Role
                    </p>

                    <h3 class="mt-2 text-2xl font-black capitalize text-slate-900">
                        {{ auth()->user()->role }}
                    </h3>
                </div>

                <div class="rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Wallet Status
                    </p>

                    <h3 class="mt-2 text-2xl font-black text-emerald-600">
                        Active
                    </h3>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Agent Access
                    </p>

                    <h3 class="mt-2 text-2xl font-black text-blue-600">
                        Downline Control
                    </h3>
                </div>
            </section>

            {{-- Management Modules --}}
            <section>
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">
                            Agent Modules
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Select a module to manage commissions, players, registrations, and withdrawals.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">

                    {{-- Commission Dashboard --}}
                    <a href="{{ route('agent.commissions.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-yellow-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-yellow-400 to-amber-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-yellow-50 text-2xl text-yellow-700">
                                    📊
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-yellow-50 group-hover:text-yellow-700">
                                    REPORT
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Commission Dashboard
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                View your commission from player bets, Pokémon battles, downline activity, and wallet totals.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-yellow-600">
                                    View commissions
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-yellow-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Player Registration Codes --}}
                    <a href="{{ route('agent.player-codes.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-700">
                                    🎟️
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-blue-50 group-hover:text-blue-700">
                                    CODES
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Player Registration Codes
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Generate direct registration codes for players who should be connected under your agent account.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-blue-600">
                                    Manage codes
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- User Management --}}
                    <a href="{{ route('agent.users.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-emerald-500 to-teal-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-700">
                                    👥
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-emerald-50 group-hover:text-emerald-700">
                                    USERS
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                User Management
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                View your players, monitor downline accounts, give credits, get credits, and view transaction history.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-emerald-600">
                                    Manage players
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Request My Withdrawal --}}
                    <a href="{{ route('withdrawals.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-cyan-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-cyan-500 to-sky-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-2xl text-cyan-700">
                                    💵
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-cyan-50 group-hover:text-cyan-700">
                                    WALLET
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Request My Withdrawal
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Submit a withdrawal request from your own agent credit balance and track request status.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-cyan-600">
                                    Request withdrawal
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-cyan-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Player Withdraw Requests --}}
                    <a href="{{ route('agent.withdrawals.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-purple-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-purple-500 to-fuchsia-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-2xl text-purple-700">
                                    💸
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-purple-50 group-hover:text-purple-700">
                                    REVIEW
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Player Withdraw Requests
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Approve or reject withdrawal requests from your players and return credit when rejected.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-purple-600">
                                    Review requests
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-purple-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                </div>
            </section>

        </main>
    </div>

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