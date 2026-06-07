<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        {{-- Header --}}
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-xl text-white shadow">
                                🛡️
                            </div>

                            <div>
                                <h1 class="text-2xl font-black tracking-tight text-slate-900">
                                    Admin Dashboard
                                </h1>

                                <p class="mt-1 text-sm text-slate-500">
                                    Manage users, reports, withdrawals, and system records.
                                </p>
                            </div>
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
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900"></div>

                    <div class="relative grid grid-cols-1 gap-6 px-6 py-8 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-blue-200">
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
                                Use the modules below to manage the platform operations.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 px-6 py-5 backdrop-blur">
                            <p class="text-sm font-bold text-slate-300">
                                System Status
                            </p>

                            <div class="mt-2 flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-400"></span>

                                <p class="text-2xl font-black text-white">
                                    Active
                                </p>
                            </div>
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
                        Platform Status
                    </p>

                    <h3 class="mt-2 text-2xl font-black text-emerald-600">
                        Online
                    </h3>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">
                        Admin Access
                    </p>

                    <h3 class="mt-2 text-2xl font-black text-blue-600">
                        Full Control
                    </h3>
                </div>
            </section>

            {{-- Management Modules --}}
            <section>
                <div class="mb-5 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-slate-900">
                            Management Modules
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Select a module to manage users, reports, withdrawals, and registrations.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">

                    {{-- User Management --}}
                    <a href="{{ route('admin.users.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-blue-500 to-indigo-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-700">
                                    👥
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-blue-50 group-hover:text-blue-700">
                                    OPEN
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                User Management
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Approve users, update roles, manage credits, edit user information, reset passwords, and force logout sessions.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-blue-600">
                                    Manage users
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Player Registration Link --}}
                    <a href="{{ route('admin.player-registration-link') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-purple-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-purple-500 to-fuchsia-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-2xl text-purple-700">
                                    🔗
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-purple-50 group-hover:text-purple-700">
                                    LINK
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Player Registration Link
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Copy the public player registration link. Players registered here will not be assigned under any agent.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-purple-600">
                                    View link
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-purple-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Withdraw Requests --}}
                    <a href="{{ route('admin.withdrawals.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-red-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-red-500 to-rose-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-red-50 text-2xl text-red-700">
                                    💸
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-red-50 group-hover:text-red-700">
                                    REVIEW
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Withdraw Requests
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                Review, approve, or reject withdrawal requests from agents and players with full payment details.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-red-600">
                                    Review requests
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-red-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Daily Sales Report --}}
                    <a href="{{ route('admin.commissions.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-emerald-500 to-green-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-2xl text-emerald-700">
                                    📊
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-emerald-50 group-hover:text-emerald-700">
                                    DAILY
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Daily Sales Report
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                View daily bets, commissions, company earnings, Pokémon reports, and agent withdrawal totals.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-emerald-600">
                                    View daily report
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                    {{-- Event Sales Report --}}
                    <a href="{{ route('admin.sales.index') }}"
                       class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:border-orange-300 hover:shadow-lg">
                        <div class="h-2 bg-gradient-to-r from-orange-500 to-amber-600"></div>

                        <div class="p-6">
                            <div class="mb-5 flex items-start justify-between gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-2xl text-orange-700">
                                    📈
                                </div>

                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500 transition group-hover:bg-orange-50 group-hover:text-orange-700">
                                    EVENT
                                </span>
                            </div>

                            <h3 class="text-xl font-black text-slate-900">
                                Event Sales Report
                            </h3>

                            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                                View sales per event, total player bets, commissions, payouts, net sales, and game performance.
                            </p>

                            <div class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4">
                                <span class="text-sm font-bold text-orange-600">
                                    View event report
                                </span>

                                <span class="text-xl text-slate-300 transition group-hover:translate-x-1 group-hover:text-orange-600">
                                    →
                                </span>
                            </div>
                        </div>
                    </a>

                </div>
            </section>

        </main>
    </div>

</body>
</html>