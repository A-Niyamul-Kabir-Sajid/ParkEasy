<x-layout title="Verification queue">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Verification queue</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Review parking lots submitted by owners and approve or reject them.
            </p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="text-sm underline">Back to dashboard</a>
    </div>

    @if ($pending->isEmpty())
        <div class="rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No parking lots are waiting for verification right now.
        </div>
    @else
        <div class="overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Lot</th>
                        <th class="px-4 py-2 font-medium">Owner</th>
                        <th class="px-4 py-2 font-medium">Rate</th>
                        <th class="px-4 py-2 font-medium">Capacity</th>
                        <th class="px-4 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pending as $row)
                        <tr class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-2">
                                <div class="font-medium">{{ $row->name }}</div>
                                @if ($row->description)
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $row->description }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div>{{ $row->owner?->name ?? '—' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $row->owner?->email }}</div>
                            </td>
                            <td class="px-4 py-2">৳{{ number_format((float) $row->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2">{{ (int) $row->available_spots }} / {{ (int) $row->total_capacity }}</td>
                            <td class="px-4 py-2 text-right">
                                <form method="POST" action="{{ route('admin.verification.approve', $row) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="rounded bg-emerald-600 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-500">
                                        Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.verification.reject', $row) }}" class="inline" onsubmit="return confirm('Reject this parking lot?');">
                                    @csrf
                                    <button type="submit" class="ml-1 rounded bg-rose-600 px-3 py-1 text-xs font-medium text-white hover:bg-rose-500">
                                        Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($recent->isNotEmpty())
        <h2 class="mt-10 mb-2 text-lg font-semibold">Recent decisions</h2>
        <div class="overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Lot</th>
                        <th class="px-4 py-2 font-medium">Owner</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent as $row)
                        <tr class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-2">{{ $row->name }}</td>
                            <td class="px-4 py-2">{{ $row->owner?->name ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $statusStyles = match ($row->verification_status->value) {
                                        'verified' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100',
                                        'rejected' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
                                        default    => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-100',
                                    };
                                @endphp
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium {{ $statusStyles }}">
                                    {{ ucfirst($row->verification_status->value) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>