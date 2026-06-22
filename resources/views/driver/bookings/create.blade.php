<x-layout title="Book {{ $parkingLot->name }}">
    <div class="mb-4">
        <a href="{{ route('parking-lots.show', $parkingLot) }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to {{ $parkingLot->name }}
        </a>
    </div>

    <div class="rounded border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-2xl font-semibold">Book a spot</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            {{ $parkingLot->name }} &mdash; ৳{{ number_format((float) $parkingLot->hourly_rate, 2) }} per hour
        </p>
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {{ (int) $parkingLot->available_spots }} / {{ (int) $parkingLot->total_capacity }} spots available
        </p>

        <form method="POST" action="{{ route('driver.bookings.store', $parkingLot) }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="start_time" class="block text-sm font-medium">Start time</label>
                <input
                    type="datetime-local"
                    id="start_time"
                    name="start_time"
                    value="{{ old('start_time', $minStart) }}"
                    min="{{ $minStart }}"
                    required
                    class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                @error('start_time')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="duration_hours" class="block text-sm font-medium">Duration (hours)</label>
                <input
                    type="number"
                    id="duration_hours"
                    name="duration_hours"
                    value="{{ old('duration_hours', 1) }}"
                    min="1"
                    max="24"
                    required
                    class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950"
                >
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Up to 24 hours per booking.</p>
                @error('duration_hours')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </div>

            <input type="hidden" name="parking_lot_id" value="{{ $parkingLot->id }}">

            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                    Confirm booking
                </button>
                <a href="{{ route('parking-lots.show', $parkingLot) }}" class="text-sm text-slate-600 underline dark:text-slate-400">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layout>