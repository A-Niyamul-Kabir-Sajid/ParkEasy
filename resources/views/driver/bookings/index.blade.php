<x-layout title="My bookings">
    <h1 class="text-2xl font-semibold">My bookings</h1>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
        All your reservations, from the next upcoming one to past visits.
    </p>

    @if ($bookings->isEmpty())
        <div class="mt-8 rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
            You have no bookings yet. <a href="{{ route('parking-lots.browse') }}" class="underline">Find a parking lot</a> to get started.
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded border border-slate-200 dark:border-slate-800">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <tr>
                        <th class="px-4 py-2 font-medium">Parking lot</th>
                        <th class="px-4 py-2 font-medium">Start</th>
                        <th class="px-4 py-2 font-medium">End</th>
                        <th class="px-4 py-2 font-medium">Status</th>
                        <th class="px-4 py-2 font-medium text-right">Actions</th>
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
                            <td class="px-4 py-2">
                                <a href="{{ route('parking-lots.show', $booking->parkingLot) }}" class="underline">
                                    {{ $booking->parkingLot?->name ?? 'Lot removed' }}
                                </a>
                            </td>
                            <td class="px-4 py-2">{{ $booking->start_time->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-2">{{ $booking->end_time->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-2">
                                <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium {{ $statusStyles }}">
                                    {{ ucfirst($booking->status->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('driver.bookings.show', $booking) }}" class="underline">View</a>
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