<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-center text-slate-800">
            Login
        </h1>

        <p class="text-center text-slate-500 mt-2">
            Welcome back
        </p>

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Username
                </label>
                <input
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    placeholder="Enter username"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >

                @error('username')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Password
                </label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter password"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >

                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition"
            >
                Login
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            No account yet?
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">
                Register
            </a>
        </p>
    </div>

</body>
</html>