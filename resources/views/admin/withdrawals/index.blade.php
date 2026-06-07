<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Withdraw Requests</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">

    <div class="min-h-screen">

        {{-- Header --}}
        <nav class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:px-8 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900">
                        Withdraw Requests
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        Review, approve, or reject agent and player withdrawal requests.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-slate-700">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
                        Users
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button class="rounded-xl bg-white px-4 py-2 text-sm font-bold text-red-600 ring-1 ring-red-200 transition hover:bg-red-50">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            {{-- Filter --}}
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <form method="GET"
                      action="{{ route('admin.withdrawals.index') }}"
                      class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_220px_auto]">

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search username or mobile number..."
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wide text-slate-500">
                            Status
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                            <option value="">All Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button class="w-full rounded-xl bg-blue-600 px-8 py-3 text-sm font-black text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 md:w-auto">
                            Filter
                        </button>
                    </div>
                </form>
            </section>

            {{-- Withdraw Requests --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-center">
                        <div>
                            <h2 class="text-lg font-black text-slate-900">
                                Requests
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Pending and processed withdrawal requests are listed below.
                            </p>
                        </div>

                        <span class="w-fit rounded-full bg-slate-100 px-4 py-2 text-xs font-black uppercase tracking-wide text-slate-600">
                            {{ ($withdrawRequests instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $withdrawRequests->total() : count($withdrawRequests ?? []) }} Records
                        </span>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($withdrawRequests as $withdrawRequest)
                        <div class="p-5 transition hover:bg-slate-50">
                            <div class="grid grid-cols-1 gap-5 xl:grid-cols-[1.1fr_1.1fr_1fr]">

                                {{-- Requester --}}
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h3 class="text-2xl font-black text-slate-900">
                                            ₱{{ number_format($withdrawRequest->amount, 2) }}
                                        </h3>

                                        @if ($withdrawRequest->status === 'approved')
                                            <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-black text-green-700 ring-1 ring-green-100">
                                                Approved
                                            </span>
                                        @elseif ($withdrawRequest->status === 'rejected')
                                            <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-black text-red-700 ring-1 ring-red-100">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="rounded-full bg-yellow-50 px-3 py-1 text-xs font-black text-yellow-700 ring-1 ring-yellow-100">
                                                Pending
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-5 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Requested By
                                            </p>
                                            <p class="mt-1 font-bold text-slate-900">
                                                {{ $withdrawRequest->user?->username ?? 'Unknown' }}
                                            </p>
                                        </div>

                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Role
                                            </p>
                                            <p class="mt-1 font-bold text-slate-900">
                                                {{ ucfirst($withdrawRequest->user?->role ?? 'N/A') }}
                                            </p>
                                        </div>

                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Mobile
                                            </p>
                                            <p class="mt-1 font-bold text-slate-900">
                                                {{ $withdrawRequest->user?->mobile_number ?? 'N/A' }}
                                            </p>
                                        </div>

                                        <div class="rounded-xl bg-slate-50 p-4">
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Date
                                            </p>
                                            <p class="mt-1 font-bold text-slate-900">
                                                {{ $withdrawRequest->created_at->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Details --}}
                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <h3 class="text-sm font-black text-slate-900">
                                        Payment Details
                                    </h3>

                                    <div class="mt-4 space-y-3 text-sm">
                                        <div>
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Method
                                            </p>
                                            <p class="mt-1 font-semibold text-slate-800">
                                                {{ $withdrawRequest->payment_method ?? 'N/A' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Account Name
                                            </p>
                                            <p class="mt-1 font-semibold text-slate-800">
                                                {{ $withdrawRequest->account_name ?? 'N/A' }}
                                            </p>
                                        </div>

                                        <div>
                                            <p class="text-xs font-black uppercase text-slate-400">
                                                Account Number
                                            </p>
                                            <p class="mt-1 font-semibold text-slate-800">
                                                {{ $withdrawRequest->account_number ?? 'N/A' }}
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                            <div>
                                                <p class="text-xs font-black uppercase text-slate-400">
                                                    User Note
                                                </p>
                                                <p class="mt-1 font-semibold text-slate-800">
                                                    {{ $withdrawRequest->note ?? 'N/A' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs font-black uppercase text-slate-400">
                                                    Admin Note
                                                </p>
                                                <p class="mt-1 font-semibold text-slate-800">
                                                    {{ $withdrawRequest->admin_note ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Admin Action --}}
                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    @if ($withdrawRequest->status === 'pending')
                                        <h3 class="text-sm font-black text-slate-900">
                                            Admin Action
                                        </h3>

                                        <p class="mt-1 text-xs font-semibold text-slate-500">
                                            Approving confirms the withdrawal. Rejecting returns credit.
                                        </p>

                                        <form method="POST"
                                              action="{{ route('admin.withdrawals.approve', $withdrawRequest) }}"
                                              class="mt-4 space-y-3">
                                            @csrf
                                            @method('PATCH')

                                            <textarea
                                                name="admin_note"
                                                rows="2"
                                                placeholder="Optional admin note"
                                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold outline-none transition placeholder:text-slate-400 focus:border-green-500 focus:ring-4 focus:ring-green-100"
                                            ></textarea>

                                            <button class="w-full rounded-xl bg-green-600 px-5 py-3 text-sm font-black text-white transition hover:bg-green-700 focus:outline-none focus:ring-4 focus:ring-green-100">
                                                Approve Withdrawal
                                            </button>
                                        </form>

                                        <form method="POST"
                                              action="{{ route('admin.withdrawals.reject', $withdrawRequest) }}"
                                              class="mt-3 space-y-3">
                                            @csrf
                                            @method('PATCH')

                                            <textarea
                                                name="admin_note"
                                                rows="2"
                                                placeholder="Reason for rejection"
                                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold outline-none transition placeholder:text-slate-400 focus:border-red-500 focus:ring-4 focus:ring-red-100"
                                            ></textarea>

                                            <button class="w-full rounded-xl bg-red-600 px-5 py-3 text-sm font-black text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-100">
                                                Reject & Return Credit
                                            </button>
                                        </form>
                                    @else
                                        <div class="flex h-full min-h-[180px] items-center justify-center rounded-xl bg-slate-50 p-6 text-center">
                                            <div>
                                                <p class="text-sm font-black text-slate-700">
                                                    Request Processed
                                                </p>

                                                <p class="mt-1 text-sm text-slate-500">
                                                    This withdrawal request has already been {{ $withdrawRequest->status }}.
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center">
                            <h2 class="text-xl font-black text-slate-900">
                                No withdraw requests found
                            </h2>

                            <p class="mt-2 text-sm text-slate-500">
                                Agent and player withdraw requests will appear here.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>

            <div class="mt-6">
                {{ $withdrawRequests->links() }}
            </div>

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