<x-layout title="My parking lots">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="mb-1 text-2xl font-semibold">My parking lots</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">Lots you've submitted for verification.</p>
        </div>
        <a href="{{ route('owner.parking-lots.create') }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
            + New parking lot
        </a>
    </div>

    @if ($lots->isEmpty())
        <div class="mt-8 rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            You haven't listed any parking lots yet.
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Name</th>
                        <th class="px-4 py-2 font-medium">Hourly rate</th>
                        <th class="px-4 py-2 font-medium">Capacity</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lots as $row)
                        <tr class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-2">{{ $row->name }}</td>
                            <td class="px-4 py-2">৳{{ number_format((float) $row->hourly_rate, 2) }}</td>
                            <td class="px-4 py-2">{{ (int) $row->available_spots }} / {{ (int) $row->total_capacity }}</td>
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
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('owner.parking-lots.edit', $row) }}" class="underline">Edit</a>
                                <form method="POST" action="{{ route('owner.parking-lots.destroy', $row) }}" class="inline"
                                    onsubmit="return confirm('Delete this parking lot?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ml-2 text-rose-600 underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layout>