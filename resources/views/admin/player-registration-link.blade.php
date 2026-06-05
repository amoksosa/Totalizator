<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Registration Link</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Player Registration Link
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Share this link for normal player registration.
                </p>
            </div>

            <a href="{{ route('admin.dashboard') }}"
               class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-semibold transition">
                Back to Dashboard
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8">

        <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
            <div class="mb-6">
                <div class="h-12 w-12 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl mb-4">
                    🔗
                </div>

                <h2 class="text-xl font-bold text-slate-900">
                    Public Player Registration
                </h2>

                <p class="text-slate-500 mt-2">
                    Players who register using this link will be normal players only.
                    They will not be under any agent downline.
                </p>
            </div>

            @php
                $playerRegisterLink = route('register', ['type' => 'player']);
            @endphp

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">
                    Registration Link
                </label>

                <div class="flex flex-col md:flex-row gap-3">
                    <input
                        id="playerRegisterLink"
                        type="text"
                        value="{{ $playerRegisterLink }}"
                        readonly
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 bg-slate-50 text-slate-700"
                    >

                    <button
                        type="button"
                        onclick="copyPlayerLink()"
                        class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-semibold transition">
                        Copy Link
                    </button>
                </div>
            </div>

            <div class="mt-6 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-800 px-5 py-4 text-sm">
                Important: players registered through this admin link should save as
                <strong>role = player</strong> and <strong>agent_id = null</strong>.
            </div>
        </section>

    </main>

    <script>
        function copyPlayerLink() {
            const input = document.getElementById('playerRegisterLink');

            navigator.clipboard.writeText(input.value).then(function () {
                alert('Player registration link copied!');
            });
        }
    </script>

</body>
</html>