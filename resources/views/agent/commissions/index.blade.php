<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Daily Sales Report</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="w-full max-w-[1500px] mx-auto px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Agent Daily Sales Report
                </h1>

                <p class="text-base text-slate-500 mt-1">
                    View your agent commission and manually convert pending commission to wallet balance.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('agent.dashboard') }}"
                   class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                    Dashboard
                </a>

                <a href="{{ route('agent.users.index') }}"
                   class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-sm font-bold transition">
                    User Management
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 text-sm font-bold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="w-full max-w-[1500px] mx-auto px-6 py-8">

        <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-5 mb-6">
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-6">
                <p class="text-sm font-bold text-emerald-700">
                    Total Commission
                </p>

                <h2 class="text-4xl font-extrabold text-emerald-800 mt-2">
                    ₱{{ number_format($totalCommission, 2) }}
                </h2>

                <p class="text-sm text-emerald-700 mt-2">
                    Commission for selected date.
                </p>
            </div>

            <div class="rounded-2xl bg-yellow-50 border border-yellow-200 p-6">
                <p class="text-sm font-bold text-yellow-700">
                    Pending Commission
                </p>

                <h2 class="text-4xl font-extrabold text-yellow-800 mt-2">
                    ₱{{ number_format($pendingCommission, 2) }}
                </h2>

                <p class="text-sm text-yellow-700 mt-2">
                    Available to convert.
                </p>
            </div>

            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-6">
                <p class="text-sm font-bold text-blue-700">
                    Converted Commission
                </p>

                <h2 class="text-4xl font-extrabold text-blue-800 mt-2">
                    ₱{{ number_format($convertedCommission, 2) }}
                </h2>

                <p class="text-sm text-blue-700 mt-2">
                    Already added to wallet.
                </p>
            </div>

            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6">
                <p class="text-sm font-bold text-slate-700">
                    Total Player Bets
                </p>

                <h2 class="text-4xl font-extrabold text-slate-900 mt-2">
                    ₱{{ number_format($totalBetAmount ?? 0, 2) }}
                </h2>

                <p class="text-sm text-slate-500 mt-2">
                    Bet volume from your players.
                </p>
            </div>

            <div class="rounded-2xl bg-purple-50 border border-purple-200 p-6">
                <p class="text-sm font-bold text-purple-700">
                    Wallet Balance
                </p>

                <h2 class="text-4xl font-extrabold text-purple-800 mt-2">
                    ₱{{ number_format(auth()->user()->credit_balance ?? 0, 2) }}
                </h2>

                <p class="text-sm text-purple-700 mt-2">
                    Used to give credit to players.
                </p>
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <form method="GET" action="{{ route('agent.commissions.index') }}"
                  class="grid grid-cols-1 md:grid-cols-[220px_1fr_auto_auto] gap-4">

                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search player username or mobile number..."
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                >

                <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-8 py-3 transition">
                    Filter
                </button>

                <a href="{{ route('agent.commissions.index') }}"
                   class="rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold px-8 py-3 transition text-center">
                    Reset
                </a>
            </form>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Convert Commission to Wallet
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Manually input how much pending commission you want to convert for
                        <span class="font-bold text-slate-900">
                            {{ $selectedDate ?? now()->format('Y-m-d') }}
                        </span>.
                    </p>

                    <p class="text-sm text-slate-500 mt-1">
                        Available pending commission:
                        <span class="font-bold text-yellow-600">
                            ₱{{ number_format($pendingCommission, 2) }}
                        </span>
                    </p>
                </div>

                @if ($pendingCommission > 0)
                    <form
                        id="convertCommissionForm"
                        method="POST"
                        action="{{ route('agent.commissions.convertToWallet') }}"
                        class="w-full lg:w-auto"
                    >
                        @csrf

                        <input type="hidden" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}">

                        <div class="flex flex-col sm:flex-row gap-3">
                            <input
                                id="convertAmountInput"
                                type="number"
                                name="amount"
                                step="0.01"
                                min="1"
                                max="{{ $pendingCommission }}"
                                placeholder="Amount to convert"
                                class="w-full sm:w-64 rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                required
                            >

                            <button
                                type="submit"
                                class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 font-bold transition"
                            >
                                Convert to Wallet
                            </button>
                        </div>
                    </form>
                @else
                    <button
                        disabled
                        class="rounded-xl bg-slate-300 text-white px-6 py-3 font-bold cursor-not-allowed"
                    >
                        No Pending Commission
                    </button>
                @endif
            </div>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-900">
                    Player Bet Commission History
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Every player bet under your account automatically creates your agent commission record.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[1150px]">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Date</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Player</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Side</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Odds</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Bet Amount</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Rate</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Commission</th>
                            <th class="px-5 py-4 text-left whitespace-nowrap">Conversion</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($commissions as $commission)
                            @php
                                $convertedAmount = (float) ($commission->converted_amount ?? 0);
                                $commissionAmount = (float) $commission->commission_amount;
                                $remainingAmount = max(0, $commissionAmount - $convertedAmount);
                            @endphp

                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4 whitespace-nowrap text-slate-600">
                                    {{ $commission->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-bold text-slate-900">
                                        {{ $commission->player?->username ?? 'Unknown Player' }}
                                    </div>

                                    <div class="text-xs text-slate-500">
                                        Mobile: {{ $commission->player?->mobile_number ?? 'N/A' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($commission->side === 'MERON')
                                        <span class="inline-flex rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            MERON
                                        </span>
                                    @elseif ($commission->side === 'WALA')
                                        <span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            WALA
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            DRAW
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 font-bold text-slate-700">
                                    {{ $commission->odds }}
                                </td>

                                <td class="px-5 py-4 font-bold text-slate-900">
                                    ₱{{ number_format($commission->bet_amount, 2) }}
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ number_format($commission->commission_rate, 2) }}%
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-lg font-extrabold text-emerald-700">
                                        ₱{{ number_format($commission->commission_amount, 2) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @if ($commission->conversion_status === 'converted')
                                        <span class="inline-flex rounded-full bg-emerald-100 text-emerald-700 px-3 py-1 text-xs font-bold">
                                            Converted
                                        </span>

                                        <p class="text-xs text-slate-500 mt-1">
                                            Converted: ₱{{ number_format($convertedAmount, 2) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            {{ $commission->converted_at?->format('M d, Y h:i A') }}
                                        </p>
                                    @elseif ($commission->conversion_status === 'partial')
                                        <span class="inline-flex rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold">
                                            Partial
                                        </span>

                                        <p class="text-xs text-slate-500 mt-1">
                                            Converted: ₱{{ number_format($convertedAmount, 2) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Remaining: ₱{{ number_format($remainingAmount, 2) }}
                                        </p>
                                    @else
                                        <span class="inline-flex rounded-full bg-yellow-100 text-yellow-700 px-3 py-1 text-xs font-bold">
                                            Pending
                                        </span>

                                        <p class="text-xs text-slate-500 mt-1">
                                            Remaining: ₱{{ number_format($remainingAmount, 2) }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <h3 class="text-xl font-bold text-slate-900">
                                        No commission history yet
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Once your players place bets, your commission records will appear here.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $commissions->links() }}
        </div>
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
                title: 'Validation Error',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('convertCommissionForm');
            const amountInput = document.getElementById('convertAmountInput');

            if (!form || !amountInput) {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const amount = Number(amountInput.value || 0);
                const maxAmount = Number(amountInput.getAttribute('max') || 0);

                if (amount <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Amount',
                        text: 'Please enter the amount you want to convert.',
                        confirmButtonColor: '#dc2626'
                    });

                    return;
                }

                if (amount > maxAmount) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Amount Too High',
                        text: 'You cannot convert more than your pending commission.',
                        confirmButtonColor: '#dc2626'
                    });

                    return;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'Convert Commission?',
                    html: 'Are you sure you want to convert <b>₱' + amount.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }) + '</b> to your wallet?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, convert',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#16a34a',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>

</body>
</html>