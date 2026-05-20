<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Withdraw Requests</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-[1500px] mx-auto px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Withdraw Requests
                </h1>

                <p class="text-sm text-slate-500 mt-1">
                    Approve or reject agent and player withdrawal requests.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                    Dashboard
                </a>

                <a href="{{ route('admin.users.index') }}"
                   class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 text-sm font-bold transition">
                    Users
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

    <main class="max-w-[1500px] mx-auto px-6 py-8">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <form method="GET"
                  action="{{ route('admin.withdrawals.index') }}"
                  class="grid grid-cols-1 md:grid-cols-[1fr_220px_auto] gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search username or mobile number..."
                    class="rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <select
                    name="status"
                    class="rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>

                <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 transition">
                    Filter
                </button>
            </form>
        </section>

        <section class="space-y-5">
            @forelse ($withdrawRequests as $withdrawRequest)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="grid grid-cols-1 xl:grid-cols-[1.2fr_1fr_1.2fr] gap-6">

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-2xl font-extrabold text-slate-900">
                                    ₱{{ number_format($withdrawRequest->amount, 2) }}
                                </h2>

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
                            </div>

                            <div class="mt-4 space-y-2 text-sm text-slate-600">
                                <p>
                                    <span class="font-bold text-slate-900">Requested By:</span>
                                    {{ $withdrawRequest->user?->username ?? 'Unknown' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Role:</span>
                                    {{ ucfirst($withdrawRequest->user?->role ?? 'N/A') }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Mobile:</span>
                                    {{ $withdrawRequest->user?->mobile_number ?? 'N/A' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Date:</span>
                                    {{ $withdrawRequest->created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">
                            <h3 class="font-bold text-slate-900">
                                Payment Details
                            </h3>

                            <div class="mt-4 space-y-2 text-sm text-slate-600">
                                <p>
                                    <span class="font-bold text-slate-900">Method:</span>
                                    {{ $withdrawRequest->payment_method ?? 'N/A' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Account Name:</span>
                                    {{ $withdrawRequest->account_name ?? 'N/A' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Account Number:</span>
                                    {{ $withdrawRequest->account_number ?? 'N/A' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">User Note:</span>
                                    {{ $withdrawRequest->note ?? 'N/A' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Admin Note:</span>
                                    {{ $withdrawRequest->admin_note ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            @if ($withdrawRequest->status === 'pending')
                                <h3 class="font-bold text-slate-900">
                                    Admin Action
                                </h3>

                                <form method="POST"
                                      action="{{ route('admin.withdrawals.approve', $withdrawRequest) }}"
                                      class="mt-4 space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    <textarea
                                        name="admin_note"
                                        rows="2"
                                        placeholder="Optional admin note"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-500"
                                    ></textarea>

                                    <button class="w-full rounded-xl bg-green-600 hover:bg-green-700 text-white px-5 py-3 font-bold transition">
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
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-red-500"
                                    ></textarea>

                                    <button class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white px-5 py-3 font-bold transition">
                                        Reject & Return Credit
                                    </button>
                                </form>
                            @else
                                <div class="h-full flex items-center justify-center text-center text-slate-500">
                                    This request has already been processed.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-12 text-center">
                    <h2 class="text-2xl font-bold text-slate-900">
                        No withdraw requests found
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Agent and player withdraw requests will appear here.
                    </p>
                </div>
            @endforelse
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