<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Registration Link</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Player Registration Link</h1>
                <p class="text-sm text-slate-500">Share this link with your players</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.dashboard') }}"
                   class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-8">

        @php
            $agent = auth()->user();

            $registrationLink = route('register', [
                'type' => 'player',
                'agent_code' => $agent->referral_code,
            ]);
        @endphp

        <div class="bg-white rounded-2xl shadow p-6">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-800">Your Player Registration Link</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Send this link to your players. Every player who registers using this link will be added to your downline.
                </p>
            </div>

            @if (! $agent->referral_code)
                <div class="mb-5 rounded-xl bg-red-100 border border-red-300 text-red-800 px-4 py-3 text-sm">
                    Your agent account has no referral code yet.
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Registration Link
                </label>

                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        id="registrationLink"
                        type="text"
                        value="{{ $registrationLink }}"
                        readonly
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700"
                    >

                    <button
                        type="button"
                        onclick="copyRegistrationLink()"
                        id="copyButton"
                        class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 whitespace-nowrap"
                    >
                        Copy Link
                    </button>
                </div>

                <p class="mt-3 text-xs text-slate-500">
                    Agent Code: {{ $agent->referral_code ?? 'No referral code yet' }}
                </p>
            </div>

            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 p-4">
                <h3 class="text-sm font-bold text-blue-800">How it works</h3>

                <ul class="mt-2 text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li>Share this link with your players.</li>
                    <li>Players register using this link.</li>
                    <li>They will automatically be added to your downline.</li>
                </ul>
            </div>
        </div>

    </main>

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