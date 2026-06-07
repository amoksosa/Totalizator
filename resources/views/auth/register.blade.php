<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    @php
        $type = request('type', session('registration_type', 'player'));
        $agentCodeFromLink = request('agent_code', session('registration_agent_code'));
        $isPlayerAgentLink = $type === 'player' && $agentCodeFromLink;
    @endphp

    <main class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-md">

            <div class="mb-6 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-2xl text-white shadow-sm">
                    ✨
                </div>

                <h1 class="mt-4 text-3xl font-black text-slate-900">
                    Create Account
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Register your account to continue.
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

                @if ($isPlayerAgentLink)
                    <div class="mb-5 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
                        You are registering as a player under an agent.
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        Please check the form and try again.
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    <input
                        type="hidden"
                        name="type"
                        value="{{ old('type', request('type', session('registration_type', 'player'))) }}"
                    >

                    <input
                        type="hidden"
                        name="agent_code"
                        value="{{ old('agent_code', request('agent_code', session('registration_agent_code'))) }}"
                    >

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Mobile Number
                        </label>

                        <input
                            type="text"
                            name="mobile_number"
                            value="{{ old('mobile_number') }}"
                            placeholder="09XXXXXXXXX"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required
                        >

                        @error('mobile_number')
                            <p class="mt-2 text-sm font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Username
                        </label>

                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            placeholder="Enter username"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required
                        >

                        @error('username')
                            <p class="mt-2 text-sm font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            placeholder="Enter password"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            required
                        >

                        @error('password')
                            <p class="mt-2 text-sm font-semibold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if (! $isPlayerAgentLink)
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Agent Registration Code
                            </label>

                            <input
                                type="text"
                                name="admin_agent_code"
                                value="{{ old('admin_agent_code') }}"
                                placeholder="Optional. For agent registration only."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >

                            <p class="mt-2 text-xs text-slate-500">
                                Leave this blank if you are registering as a normal player.
                            </p>

                            @error('admin_agent_code')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endif

                    @if ($isPlayerAgentLink)
                        <div>
                            <label class="mb-2 block text-sm font-bold text-slate-700">
                                Agent Link Code
                            </label>

                            <input
                                type="text"
                                value="{{ $agentCodeFromLink }}"
                                readonly
                                class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-semibold text-slate-600"
                            >

                            <p class="mt-2 text-xs text-slate-500">
                                This player account will be added to the agent’s downline.
                            </p>

                            @error('agent_code')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        Register
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-bold text-blue-600 hover:underline">
                        Login
                    </a>
                </p>

            </div>

        </div>

    </main>

</body>
</html>