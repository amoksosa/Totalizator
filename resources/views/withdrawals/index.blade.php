<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Request</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Withdraw Request
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Request withdrawal from your available credit balance.
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if (auth()->user()->role === 'agent')
                    <a href="{{ route('agent.dashboard') }}"
                       class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('player.dashboard') }}"
                       class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                        Game
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        <section class="grid grid-cols-1 lg:grid-cols-[0.9fr_1.4fr] gap-6">
            <div class="space-y-6">
                <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6">
                    <p class="text-sm font-bold text-emerald-700">
                        Available Credit Balance
                    </p>

                    <h2 class="text-4xl font-extrabold text-emerald-800 mt-2">
                        ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                    </h2>

                    <p class="text-sm text-emerald-700 mt-2">
                        Requested withdrawal amount will be deducted while pending.
                    </p>
                </div>

                <div class="rounded-2xl bg-white border border-slate-200 p-6">
                    <h2 class="text-2xl font-bold text-slate-900">
                        New Withdraw Request
                    </h2>

                    <form method="POST" action="{{ route('withdrawals.store') }}" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Amount
                            </label>

                            <input
                                type="number"
                                name="amount"
                                step="0.01"
                                min="1"
                                placeholder="Enter amount"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Payment Method
                            </label>

                            <input
                                type="text"
                                name="payment_method"
                                placeholder="Example: GCash, Maya, Bank"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Account Name
                            </label>

                            <input
                                type="text"
                                name="account_name"
                                placeholder="Account name"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Account Number
                            </label>

                            <input
                                type="text"
                                name="account_number"
                                placeholder="Account number / mobile number"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">
                                Note
                            </label>

                            <textarea
                                name="note"
                                rows="3"
                                placeholder="Optional note"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            ></textarea>
                        </div>

                        <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 font-bold transition">
                            Submit Withdraw Request
                        </button>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-900">
                        Withdrawal History
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-900 text-white">
                            <tr>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Amount</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Method</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Status</th>
                                <th class="px-5 py-4 text-left whitespace-nowrap">Admin Note</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-200">
                            @forelse ($withdrawRequests as $withdrawRequest)
                                <tr>
                                    <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                        {{ $withdrawRequest->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    <td class="px-5 py-4 font-bold text-slate-900 whitespace-nowrap">
                                        ₱{{ number_format($withdrawRequest->amount, 2) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $withdrawRequest->payment_method ?? 'N/A' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($withdrawRequest->status === 'approved')
                                            <span class="rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                                Approved
                                            </span>
                                        @elseif ($withdrawRequest->status === 'rejected')
                                            <span class="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $withdrawRequest->admin_note ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                        No withdraw requests yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6">
                    {{ $withdrawRequests->links() }}
                </div>
            </div>
        </section>
    </main>

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