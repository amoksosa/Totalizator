<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <nav class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">
                Admin Dashboard
            </h1>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow p-8">
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome Admin, {{ auth()->user()->username }}!
            </h2>

            <p class="text-slate-600 mt-2">
                Role: {{ auth()->user()->role }}
            </p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('admin.users.index') }}"
                   class="block rounded-2xl bg-blue-600 hover:bg-blue-700 text-white p-6 transition">
                    <h3 class="text-lg font-bold">
                        User Management
                    </h3>

                    <p class="text-sm text-blue-100 mt-1">
                        Approve users, change roles, edit info, change passwords, and force logout.
                    </p>
                </a>

                <a href="{{ route('admin.agent-codes.index') }}"
                   class="block rounded-2xl bg-purple-600 hover:bg-purple-700 text-white p-6 transition">
                    <h3 class="text-lg font-bold">
                        Agent Registration Codes
                    </h3>

                    <p class="text-sm text-purple-100 mt-1">
                        Generate direct registration codes for agents.
                    </p>
                </a>
            </div>
        </div>
    </main>

</body>
</html>