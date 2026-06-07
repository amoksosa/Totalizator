<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <main class="min-h-screen flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-md">

            <div class="mb-6 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-600 text-2xl text-white shadow-sm">
                    🔐
                </div>

                <h1 class="mt-4 text-3xl font-black text-slate-900">
                    Login
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Welcome back. Please login to continue.
                </p>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

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

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                    >
                        Login
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-slate-600">
                    No account yet?
                    <a href="{{ route('register') }}" class="font-bold text-blue-600 hover:underline">
                        Register
                    </a>
                </p>

            </div>

        </div>

    </main>

</body>
</html>