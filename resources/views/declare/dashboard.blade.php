<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Declare Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Declare Dashboard
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Select the declared winner for the current round.
                </p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                Declare Winner
            </h2>

            <p class="text-slate-500 mt-1">
                Once declared, all open player dashboards will receive the result in real time.
            </p>

            <form method="POST" action="{{ route('declare.winner.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">
                        Round Code / Fight Number
                    </label>

                    <input
                        type="text"
                        name="round_code"
                        value="{{ old('round_code') }}"
                        placeholder="Example: Fight #1"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <button
                        name="winner"
                        value="MERON"
                        class="rounded-2xl bg-red-600 hover:bg-red-700 text-white px-6 py-8 text-3xl font-extrabold transition"
                    >
                        MERON
                    </button>

                    <button
                        name="winner"
                        value="WALA"
                        class="rounded-2xl bg-blue-600 hover:bg-blue-700 text-white px-6 py-8 text-3xl font-extrabold transition"
                    >
                        WALA
                    </button>

                    <button
                        name="winner"
                        value="DRAW"
                        class="rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-8 text-3xl font-extrabold transition"
                    >
                        DRAW
                    </button>
                </div>
            </form>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">
                    Declaration History
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-left">Date</th>
                            <th class="px-5 py-4 text-left">Round</th>
                            <th class="px-5 py-4 text-left">Winner</th>
                            <th class="px-5 py-4 text-left">Declared By</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($declarations as $declaration)
                            <tr>
                                <td class="px-5 py-4">
                                    {{ $declaration->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $declaration->round_code ?? 'N/A' }}
                                </td>

                                <td class="px-5 py-4">
                                    @if ($declaration->winner === 'MERON')
                                        <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif ($declaration->winner === 'WALA')
                                        <span class="rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @else
                                        <span class="rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    {{ $declaration->declarer?->username ?? 'Unknown' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                    No declarations yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $declarations->links() }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Winner Declared',
                text: @json(session('success')),
                confirmButtonColor: '#16a34a'
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

</body>
</html>