<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-40">
        <div class="w-full max-w-[1600px] mx-auto px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">
                    Admin User Management
                </h1>

                <p class="text-base text-slate-500 mt-1">
                    Manage approvals, roles, agent credits, user profiles, and account access.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-5 py-3 text-sm font-bold transition">
                    Dashboard
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

    <main class="w-full max-w-[1600px] mx-auto px-6 py-8">

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.index') }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search username, mobile, role..."
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                >

                <select
                    name="role"
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Roles</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="agent" @selected(request('role') === 'agent')>Agent</option>
                    <option value="player" @selected(request('role') === 'player')>Player</option>
                    <option value="declare" @selected(request('role') === 'declare')>Declare</option>
                </select>

                <select
                    name="status"
                    class="rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="disapproved" @selected(request('status') === 'disapproved')>Deactivated</option>
                </select>

                <button class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-base transition">
                    Filter
                </button>
            </form>
        </section>

        <section class="space-y-5">
            @forelse ($users as $user)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="grid grid-cols-1 xl:grid-cols-[1.5fr_1.1fr_1.4fr_1.1fr] gap-5 p-6 items-start">

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-2xl font-extrabold text-slate-950 break-words">
                                    {{ $user->username }}
                                </h2>

                                @if ($user->status === 'approved')
                                    <span class="inline-flex rounded-full bg-green-100 text-green-700 px-4 py-1.5 text-sm font-extrabold">
                                        Approved
                                    </span>
                                @elseif ($user->status === 'disapproved')
                                    <span class="inline-flex rounded-full bg-red-100 text-red-700 px-4 py-1.5 text-sm font-extrabold">
                                        Deactivated
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-yellow-100 text-yellow-700 px-4 py-1.5 text-sm font-extrabold">
                                        Pending
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 space-y-1 text-base text-slate-600">
                                <p>
                                    <span class="font-bold text-slate-900">Mobile:</span>
                                    {{ $user->mobile_number }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">User ID:</span>
                                    {{ $user->id }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Agent:</span>
                                    {{ $user->agent?->username ?? 'None' }}
                                </p>

                                <p>
                                    <span class="font-bold text-slate-900">Created:</span>
                                    {{ $user->created_at?->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Role
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Current role:
                                <span class="font-bold capitalize text-slate-900">
                                    {{ $user->role }}
                                </span>
                            </p>

                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="mt-4 flex gap-2">
                                @csrf
                                @method('PATCH')

                                <select
                                    name="role"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="agent" @selected($user->role === 'agent')>Agent</option>
                                    <option value="player" @selected($user->role === 'player')>Player</option>
                                    <option value="declare" @selected($user->role === 'declare')>Declare</option>
                                </select>

                                <button class="rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 text-sm font-bold transition">
                                    Save
                                </button>
                            </form>
                        </div>

                        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">
                            <h3 class="text-lg font-extrabold text-emerald-900">
                                Credit Balance
                            </h3>

                            <p class="text-4xl font-extrabold text-emerald-800 mt-3">
                                ₱<span id="admin-user-balance-{{ $user->id }}">
                                    {{ number_format($user->credit_balance ?? 0, 2) }}
                                </span>
                            </p>

                            @if ($user->role === 'agent')
                                <p class="text-sm text-emerald-700 mt-3">
                                    Add credits to this agent so they can distribute credits to their players.
                                </p>

                                <form
                                    method="POST"
                                    action="{{ route('admin.users.giveCredit', $user) }}"
                                    class="mt-4 flex flex-col sm:flex-row gap-2"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <input
                                        type="number"
                                        name="credit_amount"
                                        step="0.01"
                                        min="1"
                                        placeholder="Amount"
                                        class="w-full rounded-xl border border-emerald-300 bg-white px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500"
                                        required
                                    >

                                    <button class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-3 text-sm font-bold transition whitespace-nowrap">
                                        Give Credit
                                    </button>
                                </form>
                            @else
                                <p class="text-sm text-slate-500 mt-3">
                                    Credits can only be given to agents.
                                </p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Actions
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                Edit profile or manage account access.
                            </p>

                            <div class="mt-4 grid grid-cols-1 gap-3">
                                <button
                                    type="button"
                                    onclick="openUserModal('user-modal-{{ $user->id }}')"
                                    class="w-full rounded-xl bg-slate-700 hover:bg-slate-800 text-white px-4 py-3 text-sm font-bold transition"
                                >
                                    Edit User Profile
                                </button>

                                @if ($user->status === 'approved')
                                    <form method="POST" action="{{ route('admin.users.disapprove', $user) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button class="w-full rounded-xl bg-red-600 hover:bg-red-700 text-white px-4 py-3 text-sm font-bold transition">
                                            Deactivate Account
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                                        @csrf
                                        @method('PATCH')

                                        <button class="w-full rounded-xl bg-green-600 hover:bg-green-700 text-white px-4 py-3 text-sm font-bold transition">
                                            Approve Account
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.users.forceLogout', $user) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="w-full rounded-xl bg-slate-900 hover:bg-black text-white px-4 py-3 text-sm font-bold transition">
                                        Force Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-12 text-center">
                    <h2 class="text-2xl font-bold text-slate-900">
                        No users found
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Try changing your filters or search keyword.
                    </p>
                </div>
            @endforelse
        </section>

        @foreach ($users as $user)
            <div
                id="user-modal-{{ $user->id }}"
                class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4"
            >
                <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200">
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-900">
                                Edit User Profile
                            </h2>

                            <p class="text-base text-slate-500 mt-1">
                                {{ $user->username }} — {{ ucfirst($user->role) }}
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closeUserModal('user-modal-{{ $user->id }}')"
                            class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-3 text-sm font-bold"
                        >
                            ✕
                        </button>
                    </div>

                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Edit Information
                            </h3>

                            <p class="text-base text-slate-500 mt-1">
                                Update username and mobile number.
                            </p>

                            <form method="POST" action="{{ route('admin.users.info', $user) }}" class="mt-4 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        Username
                                    </label>

                                    <input
                                        type="text"
                                        name="username"
                                        value="{{ $user->username }}"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        Mobile Number
                                    </label>

                                    <input
                                        type="text"
                                        name="mobile_number"
                                        value="{{ $user->mobile_number }}"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                    >
                                </div>

                                <button class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 text-sm font-bold transition">
                                    Save Information
                                </button>
                            </form>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h3 class="text-lg font-extrabold text-slate-900">
                                Change Password
                            </h3>

                            <p class="text-base text-slate-500 mt-1">
                                Set a new password for this user.
                            </p>

                            <form method="POST" action="{{ route('admin.users.password', $user) }}" class="mt-4 space-y-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1">
                                        New Password
                                    </label>

                                    <input
                                        type="password"
                                        name="password"
                                        placeholder="Enter new password"
                                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-base focus:outline-none focus:ring-2 focus:ring-orange-500"
                                        required
                                    >
                                </div>

                                <button class="w-full rounded-xl bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 text-sm font-bold transition">
                                    Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function openUserModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }

        function closeUserModal(id) {
            const modal = document.getElementById(id);

            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('[id^="user-modal-"]').forEach(function (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                });
            }
        });
    </script>

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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.Echo) {
            console.error('Laravel Echo is not loaded.');
            return;
        }

        function formatMoney(value) {
            return Number(value || 0).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        window.Echo.channel('admin.balances')
            .listen('.credit.updated', function (event) {
                const balanceElement = document.getElementById('admin-user-balance-' + event.user_id);

                if (balanceElement) {
                    balanceElement.textContent = formatMoney(event.credit_balance);
                }

                if (event.role === 'player' && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Player Credit Updated',
                        text: event.username + ' new balance is ' + event.formatted_balance,
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
    });
</script>

</body>
</html>