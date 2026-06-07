<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Registration Link</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <div class="min-h-screen">

        {{-- Header --}}
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-xl text-white shadow">
                            🎟️
                        </div>

                        <div>
                            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                                Player Registration Link
                            </h1>

                            <p class="mt-1 text-sm text-slate-500">
                                Share this link with players so they register directly under your agent account.
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('agent.dashboard') }}"
                           class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                            Dashboard
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-red-700">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8">

            @php
                $agent = $agent ?? auth()->user();

                $registrationLink = route('register', [
                    'type' => 'player',
                    'agent_code' => $agent->referral_code,
                ]);
            @endphp

            {{-- Hero Card --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900"></div>

                    <div class="relative grid grid-cols-1 gap-6 px-6 py-8 lg:grid-cols-[1fr_340px] lg:items-center">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide text-blue-200">
                                Agent Player Link
                            </p>

                            <h2 class="mt-2 text-3xl font-black text-white">
                                Register Players Under Your Downline
                            </h2>

                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-300">
                                Send this registration link to your players. Every player who signs up using this link
                                will automatically be connected to your agent account.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 px-6 py-5 backdrop-blur">
                            <p class="text-sm font-bold text-slate-300">
                                Agent Code
                            </p>

                            <p class="mt-2 break-all text-2xl font-black text-white">
                                {{ $agent->referral_code ?? 'No referral code yet' }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Main Content --}}
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_360px]">

                {{-- Registration Link Card --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6">
                        <h2 class="text-xl font-black text-slate-900">
                            Your Player Registration Link
                        </h2>

                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Copy and send this link to your players. They will be added to your downline after registration.
                        </p>
                    </div>

                    @if (! $agent->referral_code)
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-700">
                            Your agent account has no referral code yet. Please contact the admin to generate one.
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <label class="mb-2 block text-sm font-black text-slate-800">
                            Registration Link
                        </label>

                        <div class="flex flex-col gap-3 md:flex-row">
                            <input
                                id="registrationLink"
                                type="text"
                                value="{{ $registrationLink }}"
                                readonly
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            >

                            <button
                                type="button"
                                onclick="copyRegistrationLink()"
                                id="copyButton"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                            >
                                Copy Link
                            </button>
                        </div>

                        <p class="mt-3 text-xs font-semibold text-slate-500">
                            Agent Code:
                            <span class="font-black text-slate-800">
                                {{ $agent->referral_code ?? 'No referral code yet' }}
                            </span>
                        </p>
                    </div>

                    <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                        <div class="flex gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white">
                                i
                            </div>

                            <div>
                                <h3 class="text-sm font-black text-blue-900">
                                    How it works
                                </h3>

                                <ul class="mt-2 space-y-1 text-sm leading-relaxed text-blue-800">
                                    <li>1. Share this link with your players.</li>
                                    <li>2. Players register using the link.</li>
                                    <li>3. They will automatically be added to your downline.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Panel --}}
                <aside class="space-y-5">
                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-black text-slate-900">
                            Link Details
                        </h3>

                        <div class="mt-5 space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                    Account Type
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    Player
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                    Downline Assignment
                                </p>

                                <p class="mt-1 text-sm font-bold text-slate-800">
                                    Under {{ $agent->username }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                    Agent Code
                                </p>

                                <p class="mt-1 break-all text-sm font-bold text-slate-800">
                                    {{ $agent->referral_code ?? 'No referral code yet' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-emerald-100 bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-black text-slate-900">
                            Next Step
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            After players register, you can manage their credits and view their transaction history in User Management.
                        </p>

                        <a href="{{ route('agent.users.index') }}"
                           class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-black text-white transition hover:bg-emerald-700">
                            Go to User Management
                        </a>
                    </div>
                </aside>

            </section>

        </main>
    </div>

    <script>
        function copyRegistrationLink() {
            const input = document.getElementById('registrationLink');
            const button = document.getElementById('copyButton');

            navigator.clipboard.writeText(input.value).then(function () {
                const oldText = button.innerText;

                button.innerText = 'Copied!';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-green-600', 'hover:bg-green-700');

                setTimeout(function () {
                    button.innerText = oldText;
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                }, 1500);
            });
        }
    </script>

</body>
</html>