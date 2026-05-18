<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-3xl font-bold text-center text-slate-800">
            Create Account
        </h1>

        <p class="text-center text-slate-500 mt-2">
            Register to continue
        </p>

        @if (session('success'))
            <div class="mt-5 rounded-xl bg-green-100 border border-green-300 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Mobile Number
                </label>
                <input
                    type="text"
                    name="mobile_number"
                    value="{{ old('mobile_number') }}"
                    placeholder="09XXXXXXXXX"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                >

                @error('mobile_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

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

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Agent Registration Code
                </label>
                <input
                    type="text"
                    name="agent_code"
                    value="{{ old('agent_code') }}"
                    placeholder="Optional. For agents only."
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <p class="text-xs text-slate-500 mt-1">
                    Leave this blank if you are registering as a player.
                </p>

                @error('agent_code')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
    <label class="block text-sm font-medium text-slate-700 mb-1">
        Player Registration Code
    </label>

    <input
        type="text"
        name="player_code"
        value="{{ old('player_code') }}"
        placeholder="Optional. Provided by agent."
        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
    >

    <p class="text-xs text-slate-500 mt-1">
        Use this if an agent gave you a player registration code.
    </p>

    @error('player_code')
        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
    @enderror
</div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition"
            >
                Register
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">
                Login
            </a>
        </p>
    </div>

</body>
</html>