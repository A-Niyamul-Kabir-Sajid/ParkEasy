<x-layout title="Bookings for {{ $parkingLot->name }}">
    <div class="mb-4">
        <a href="{{ route('owner.parking-lots.index') }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to my parking lots
        </a>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ $parkingLot->name }}</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            Incoming and past bookings on this lot.
        </p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ (int) $parkingLot->available_spots }} / {{ (int) $parkingLot->total_capacity }} spots currently available.
        </p>
    </div>

    @if ($bookings->isEmpty())
        <div class="rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            No bookings yet for this lot.
        </div>
    @else
        <div class="overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Driver</th>
                        <th class="px-4 py-2 font-medium">Start</th>
                        <th class="px-4 py-2 font-medium">End</th>
                        <th class="px-4 py-2 font-medium">Duration</th>
                        <th class="px-4 py-2 font-medium">Total</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $booking)
                        @php
                            $statusStyles = match ($booking->status->value) {
                                'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100',
                                'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
                                'completed' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
                            };
                        @endphp
                        <tr class="border-t border-slate-200 dark:border-slate-800">
                            <td class="px-4 py-2">{{ $booking->driver?->name ?? 'Driver removed' }}</td>
                            <td class="px-4 py-2">{{ $booking->start_time->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-2">{{ $booking->end_time->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-2">{{ number_format($booking->hours(), 1) }} h</td>
                            <td class="px-4 py-2">৳{{ number_format($booking->totalCost(), 2) }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium {{ $statusStyles }}">
                                    {{ ucfirst($booking->status->value) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</x-layout>