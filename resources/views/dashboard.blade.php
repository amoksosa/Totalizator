<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <nav class="bg-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-slate-800">
                Dashboard
            </h1>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold"
                >
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-10">
        <div class="bg-white rounded-2xl shadow p-8">
            <h2 class="text-2xl font-bold text-slate-800">
                Welcome, {{ auth()->user()->username }}!
            </h2>

            <p class="text-slate-600 mt-2">
                Mobile Number: {{ auth()->user()->mobile_number }}
            </p>
        </div>
    </main>

</body>
</html>