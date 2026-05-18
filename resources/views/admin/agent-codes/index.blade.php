<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Registration Codes</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">

    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Agent Registration Codes</h1>
                <p class="text-sm text-slate-500">Generate codes for direct agent registration</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}"
                   class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    Dashboard
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        @if (session('success'))
            <div class="mb-5 rounded-xl bg-green-100 border border-green-300 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow p-6 mb-6 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Create New Agent Code</h2>
                <p class="text-sm text-slate-500">
                    Give this code to an agent so they can register directly as an approved agent.
                </p>
            </div>

            <form method="POST" action="{{ route('admin.agent-codes.store') }}">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-xl font-semibold">
                    Generate Code
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">Code</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Created By</th>
                            <th class="px-4 py-3 text-left">Used By</th>
                            <th class="px-4 py-3 text-left">Created Date</th>
                            <th class="px-4 py-3 text-left">Used Date</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-200">
                        @forelse ($codes as $code)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-blue-700 text-base">
                                        {{ $code->code }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    @if ($code->is_used)
                                        <span class="inline-flex rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-bold">
                                            Used
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-bold">
                                            Available
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $code->creator?->username ?? 'N/A' }}
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $code->usedBy?->username ?? 'Not used yet' }}
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $code->created_at->format('M d, Y h:i A') }}
                                </td>

                                <td class="px-4 py-4 text-slate-700">
                                    {{ $code->used_at ? $code->used_at->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                    No agent codes yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $codes->links() }}
        </div>
    </main>

</body>
</html>