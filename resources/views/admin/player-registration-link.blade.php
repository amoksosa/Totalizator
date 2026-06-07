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
            <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-5 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-xl text-white shadow-sm">
                        🔗
                    </div>

                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900">
                            Player Registration Link
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Generate and share the public registration link for direct player sign-ups.
                        </p>
                    </div>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-slate-700">
                    Back to Dashboard
                </a>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8">

            @php
                $playerRegisterLink = route('register', ['type' => 'player']);
            @endphp

            {{-- Main Card --}}
            <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                {{-- Card Header --}}
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-blue-900 px-6 py-8">
                    <div class="max-w-3xl">
                        <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-blue-100 ring-1 ring-white/10">
                            Public Registration
                        </span>

                        <h2 class="mt-4 text-3xl font-black tracking-tight text-white">
                            Direct Player Registration
                        </h2>

                        <p class="mt-3 text-sm leading-relaxed text-slate-300">
                            Share this link with players who should register as normal players only.
                            These players will not be assigned to any agent downline.
                        </p>
                    </div>
                </div>

                {{-- Link Area --}}
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_320px]">

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-800">
                                Registration Link
                            </label>

                            <div class="flex flex-col gap-3 md:flex-row">
                                <input
                                    id="playerRegisterLink"
                                    type="text"
                                    value="{{ $playerRegisterLink }}"
                                    readonly
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                >

                                <button
                                    type="button"
                                    onclick="copyPlayerLink()"
                                    class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                                    Copy Link
                                </button>
                            </div>

                            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4">
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-sm font-black text-white">
                                        i
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-blue-900">
                                            Registration Rule
                                        </h3>

                                        <p class="mt-1 text-sm leading-relaxed text-blue-800">
                                            Players registered through this admin link should be saved as
                                            <strong>role = player</strong> and <strong>agent_id = null</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Panel --}}
                        <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <h3 class="text-sm font-black text-slate-900">
                                Link Details
                            </h3>

                            <div class="mt-4 space-y-4">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                        Account Type
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        Player
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                        Agent Assignment
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        No Agent
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                                        Registration Status
                                    </p>

                                    <p class="mt-1 text-sm font-bold text-slate-800">
                                        Pending approval after registration
                                    </p>
                                </div>
                            </div>
                        </aside>

                    </div>
                </div>
            </section>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function copyPlayerLink() {
            const input = document.getElementById('playerRegisterLink');

            navigator.clipboard.writeText(input.value).then(function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied',
                        text: 'Player registration link copied successfully.',
                        timer: 1600,
                        showConfirmButton: false,
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    alert('Player registration link copied!');
                }
            });
        }
    </script>

</body>
</html>