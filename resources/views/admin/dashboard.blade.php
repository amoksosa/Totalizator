<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Admin Dashboard
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Manage users, reports, withdrawals, and system records.
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
                        Welcome Admin, {{ auth()->user()->username }}!
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
                        System Status
                    </p>

                    <p class="text-lg font-bold text-emerald-800">
                        Active
                    </p>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

            <a href="{{ route('admin.users.index') }}"
               class="group block bg-white border border-slate-200 hover:border-blue-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl mb-4">
                            👥
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            User Management
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Approve users, change roles, edit user info, change passwords, and force logout.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-blue-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.player-registration-link') }}"
               class="group block bg-white border border-slate-200 hover:border-purple-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl mb-4">
                            🔗
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Player Registration Link
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Copy the public player registration link. Players registered here will not be under any agent.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-purple-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.withdrawals.index') }}"
               class="group block bg-white border border-slate-200 hover:border-red-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-red-100 text-red-700 flex items-center justify-center text-2xl mb-4">
                            💸
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Withdraw Requests
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            Approve or reject agent and player withdrawal requests.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-red-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.commissions.index') }}"
               class="group block bg-white border border-slate-200 hover:border-emerald-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mb-4">
                            📊
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Daily Sales Report
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            View daily bets, commissions, company earnings, and agent withdraw totals.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-emerald-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.sales.index') }}"
               class="group block bg-white border border-slate-200 hover:border-orange-400 rounded-2xl shadow-sm hover:shadow-md p-6 transition">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="h-12 w-12 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-2xl mb-4">
                            📈
                        </div>

                        <h3 class="text-lg font-bold text-slate-900">
                            Event Sales Report
                        </h3>

                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                            View sales report per event, total player bets, commissions, payouts, and net sales.
                        </p>
                    </div>

                    <span class="text-slate-300 group-hover:text-orange-600 transition text-xl">
                        →
                    </span>
                </div>
            </a>

        </section>

    </main>

</body>
</html>