<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Request</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-950 text-white">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,#065f46_0,#020617_45%,#020617_100%)]">

        {{-- Navbar --}}
        <nav class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/85 backdrop-blur-xl">
            <div class="mx-auto max-w-7xl px-4 py-4">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div>
                        <h1 class="text-2xl font-black tracking-tight text-white">
                            Withdraw Request
                        </h1>

                        <p class="mt-1 text-sm text-slate-400">
                            Request withdrawal from your available credit balance.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">

                        {{-- Balance --}}
                        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 shadow-lg shadow-emerald-950/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-lg">
                                    💰
                                </div>

                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-wide text-emerald-300">
                                        Balance
                                    </p>

                                    <p class="text-sm font-black text-white">
                                        ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if (auth()->user()->role === 'agent')
                            <a href="{{ route('agent.dashboard') }}"
                               class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-black text-white transition hover:bg-white/20">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('player.dashboard') }}"
                               class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm font-black text-white transition hover:bg-white/20">
                                Dashboard
                            </a>

                            <a href="{{ route('player.totalizator') }}"
                               class="rounded-2xl border border-blue-400/20 bg-blue-500/10 px-4 py-3 text-sm font-black text-blue-100 transition hover:bg-blue-500/20">
                                Totalizator
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button class="rounded-2xl bg-red-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-950/30 transition hover:bg-red-700">
                                Logout
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8">

            {{-- Header Card --}}
            <section class="mb-8 overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-2xl backdrop-blur">
                <div class="relative p-6 md:p-8">
                    <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl"></div>

                    <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_340px] lg:items-center">
                        <div>
                            <p class="text-sm font-black uppercase tracking-wide text-emerald-300">
                                Wallet Withdrawal
                            </p>

                            <h2 class="mt-3 text-4xl font-black text-white md:text-5xl">
                                Cash Out Request
                            </h2>

                            <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 md:text-base">
                                Submit your withdrawal details below. Once submitted, the requested amount will be deducted while pending approval.
                            </p>
                        </div>

                        <div class="rounded-3xl border border-emerald-400/20 bg-emerald-400/10 p-5">
                            <p class="text-sm font-bold text-emerald-300">
                                Available Credit Balance
                            </p>

                            <p class="mt-2 text-4xl font-black text-white">
                                ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                            </p>

                            <p class="mt-2 text-xs text-slate-400">
                                This is your current withdrawable balance.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 lg:grid-cols-[420px_1fr]">

                {{-- New Withdraw Form --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5">
                        <h2 class="text-2xl font-black text-white">
                            New Withdraw Request
                        </h2>

                        <p class="mt-1 text-sm text-slate-400">
                            Fill in your payment details carefully.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('withdrawals.store') }}" class="space-y-4 p-6">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-200">
                                Amount
                            </label>

                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-emerald-300">
                                    ₱
                                </span>

                                <input
                                    type="number"
                                    name="amount"
                                    step="0.01"
                                    min="1"
                                    placeholder="Enter amount"
                                    class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-10 py-4 text-sm font-black text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300/50 focus:ring-4 focus:ring-emerald-300/10"
                                    required
                                >
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-200">
                                Payment Method
                            </label>

                            <input
                                type="text"
                                name="payment_method"
                                placeholder="Example: GCash, Maya, Bank"
                                class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-4 text-sm font-bold text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300/50 focus:ring-4 focus:ring-emerald-300/10"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-200">
                                Account Name
                            </label>

                            <input
                                type="text"
                                name="account_name"
                                placeholder="Account name"
                                class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-4 text-sm font-bold text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300/50 focus:ring-4 focus:ring-emerald-300/10"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-200">
                                Account Number
                            </label>

                            <input
                                type="text"
                                name="account_number"
                                placeholder="Account number / mobile number"
                                class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-4 text-sm font-bold text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300/50 focus:ring-4 focus:ring-emerald-300/10"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-black text-slate-200">
                                Note
                            </label>

                            <textarea
                                name="note"
                                rows="3"
                                placeholder="Optional note"
                                class="w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-4 text-sm font-bold text-white outline-none transition placeholder:text-slate-500 focus:border-emerald-300/50 focus:ring-4 focus:ring-emerald-300/10"
                            ></textarea>
                        </div>

                        <button class="w-full rounded-2xl bg-emerald-500 px-5 py-4 text-sm font-black text-slate-950 shadow-lg shadow-emerald-950/30 transition hover:bg-emerald-400">
                            Submit Withdraw Request
                        </button>
                    </form>
                </div>

                {{-- Withdrawal History --}}
                <div class="overflow-hidden rounded-3xl border border-white/10 bg-white/10 shadow-xl backdrop-blur">
                    <div class="border-b border-white/10 px-6 py-5">
                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 class="text-2xl font-black text-white">
                                    Withdrawal History
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Track your pending, approved, and rejected requests.
                                </p>
                            </div>

                            <span class="w-fit rounded-full border border-emerald-300/20 bg-emerald-500/10 px-4 py-2 text-xs font-black uppercase tracking-wide text-emerald-200">
                                Latest Requests
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-sm">
                            <thead class="bg-slate-900/90 text-slate-200">
                                <tr>
                                    <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                                    <th class="px-5 py-4 text-right whitespace-nowrap">Amount</th>
                                    <th class="px-5 py-4 text-left whitespace-nowrap">Method</th>
                                    <th class="px-5 py-4 text-left whitespace-nowrap">Status</th>
                                    <th class="px-5 py-4 text-left whitespace-nowrap">Admin Note</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-white/10">
                                @forelse ($withdrawRequests as $withdrawRequest)
                                    <tr class="transition hover:bg-white/10">
                                        <td class="px-5 py-4 whitespace-nowrap text-slate-300">
                                            {{ $withdrawRequest->created_at->format('M d, Y h:i A') }}
                                        </td>

                                        <td class="px-5 py-4 text-right font-black text-emerald-200 whitespace-nowrap">
                                            ₱{{ number_format($withdrawRequest->amount, 2) }}
                                        </td>

                                        <td class="px-5 py-4 text-slate-300">
                                            {{ $withdrawRequest->payment_method ?? 'N/A' }}
                                        </td>

                                        <td class="px-5 py-4">
                                            @if ($withdrawRequest->status === 'approved')
                                                <span class="rounded-full bg-green-500/15 px-3 py-1 text-xs font-black text-green-200 ring-1 ring-green-400/20">
                                                    Approved
                                                </span>
                                            @elseif ($withdrawRequest->status === 'rejected')
                                                <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-black text-red-200 ring-1 ring-red-400/20">
                                                    Rejected
                                                </span>
                                            @else
                                                <span class="rounded-full bg-yellow-500/15 px-3 py-1 text-xs font-black text-yellow-200 ring-1 ring-yellow-400/20">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>

                                        <td class="px-5 py-4 text-slate-300">
                                            {{ $withdrawRequest->admin_note ?? 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-5 py-16 text-center">
                                            <div class="mx-auto max-w-md">
                                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-500/15 text-3xl ring-1 ring-emerald-400/20">
                                                    🏦
                                                </div>

                                                <h3 class="mt-5 text-2xl font-black text-white">
                                                    No withdraw requests yet
                                                </h3>

                                                <p class="mt-2 text-sm text-slate-400">
                                                    Your withdrawal requests will appear here after you submit one.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-white/10 p-6">
                        {{ $withdrawRequests->links() }}
                    </div>
                </div>

            </section>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json(session('success')),
                confirmButtonColor: '#16a34a'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                confirmButtonColor: '#dc2626'
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