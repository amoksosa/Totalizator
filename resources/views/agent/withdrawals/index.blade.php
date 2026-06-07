<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Withdraw Requests</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Player Withdraw Requests
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Approve or reject withdrawal requests from your players.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('agent.dashboard') }}"
                   class="rounded-lg bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 text-sm font-semibold transition">
                    Dashboard
                </a>

                <a href="{{ route('agent.users.index') }}"
                   class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-semibold transition">
                    Users
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-sm font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-6">

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-5">
            <form method="GET"
                  action="{{ route('agent.withdrawals.index') }}"
                  class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-3">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search username or mobile number..."
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <select
                    name="status"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>

                <button class="rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2 transition">
                    Filter
                </button>
            </form>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">
                    Withdraw Requests
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Review player withdrawal details and process pending requests.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[1200px]">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-3 text-left">Request</th>
                            <th class="px-4 py-3 text-left">Player</th>
                            <th class="px-4 py-3 text-left">Payment Method</th>
                            <th class="px-4 py-3 text-left">Account Details</th>
                            <th class="px-4 py-3 text-left">Notes</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($withdrawRequests as $withdrawRequest)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4">
                                    <p class="text-lg font-bold text-slate-900">
                                        ₱{{ number_format($withdrawRequest->amount, 2) }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ $withdrawRequest->created_at->format('M d, Y h:i A') }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <p class="font-bold text-slate-900">
                                        {{ $withdrawRequest->user?->username ?? 'Unknown' }}
                                    </p>

                                    <p class="text-xs text-slate-500 mt-1">
                                        {{ ucfirst($withdrawRequest->user?->role ?? 'N/A') }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ $withdrawRequest->user?->mobile_number ?? 'N/A' }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    {{ $withdrawRequest->payment_method ?? 'N/A' }}
                                </td>

                                <td class="px-4 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ $withdrawRequest->account_name ?? 'N/A' }}
                                    </p>

                                    <p class="text-slate-600 mt-1">
                                        {{ $withdrawRequest->account_number ?? 'N/A' }}
                                    </p>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-xs font-bold uppercase text-slate-400">
                                                User Note
                                            </p>

                                            <p class="text-slate-700">
                                                {{ $withdrawRequest->note ?? 'N/A' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-bold uppercase text-slate-400">
                                                Admin Note
                                            </p>

                                            <p class="text-slate-700">
                                                {{ $withdrawRequest->admin_note ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-4">
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

                                <td class="px-4 py-4">
                                    @if ($withdrawRequest->status === 'pending')
                                        <div class="grid grid-cols-1 gap-3 min-w-[260px]">
                                            <form method="POST"
                                                  action="{{ route('agent.withdrawals.approve', $withdrawRequest) }}"
                                                  class="space-y-2">
                                                @csrf
                                                @method('PATCH')

                                                <textarea
                                                    name="admin_note"
                                                    rows="2"
                                                    placeholder="Optional admin note"
                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                                ></textarea>

                                                <button class="w-full rounded-lg bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-xs font-bold transition">
                                                    Approve Withdrawal
                                                </button>
                                            </form>

                                            <form method="POST"
                                                  action="{{ route('agent.withdrawals.reject', $withdrawRequest) }}"
                                                  class="space-y-2"
                                                  onsubmit="return confirm('Reject this withdrawal and return credit?')">
                                                @csrf
                                                @method('PATCH')

                                                <textarea
                                                    name="admin_note"
                                                    rows="2"
                                                    placeholder="Reason for rejection"
                                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                                                ></textarea>

                                                <button class="w-full rounded-lg bg-red-600 hover:bg-red-700 text-white px-4 py-2 text-xs font-bold transition">
                                                    Reject & Return Credit
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-500">
                                            Already processed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <h2 class="text-xl font-bold text-slate-900">
                                        No withdraw requests found
                                    </h2>

                                    <p class="text-slate-500 mt-2">
                                        Player withdraw requests will appear here.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $withdrawRequests->links() }}
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
                title: 'Error',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonColor: '#dc2626'
            });
        </script>
    @endif

</body>
</html>