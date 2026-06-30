<x-layout title="Owner dashboard">
    @php
        $owner = auth()->user();
        $lots = $owner->parkingLots()->latest()->get();
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Owner dashboard</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Welcome back, {{ $owner->name }}. Manage your parking lots and reservations here.
            </p>
        </div>
        <a href="{{ route('owner.parking-lots.create') }}"
            class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
            + New parking lot
        </a>
    </div>

    <div class="mb-6">
        <a href="{{ route('owner.parking-lots.index') }}" class="text-sm underline">
            View all my parking lots ({{ $lots->count() }})
        </a>
    </div>

    @if ($lots->isEmpty())
        <div class="rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            You haven't listed any parking lots yet. Click <strong>+ New parking lot</strong> to get started.
        </div>
    @else
        <div class="overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Name</th>
                        <th class="px-4 py-2 font-medium">Rate</th>
                        <th class="px-4 py-2 font-medium">Spots</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lots->take(5) as $row)
                        <tr class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-2">{{ $row->name }}</td>
                            <td class="px-4 py-2">৳{{ number_format((float) $row->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2">{{ (int) $row->available_spots }} / {{ (int) $row->total_capacity }}</td>
                            <td class="px-4 py-2">{{ ucfirst($row->verification_status->value) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>