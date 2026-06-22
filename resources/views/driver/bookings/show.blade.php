<x-layout title="Booking #{{ $booking->id }}">
    <div class="mb-4">
        <a href="{{ route('driver.bookings.index') }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to my bookings
        </a>
    </div>

    @php
        $statusStyles = match ($booking->status->value) {
            'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100',
            'cancelled' => 'bg-rose-100 text-rose-800 dark:bg-rose-900 dark:text-rose-100',
            'completed' => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
            default => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        };
    @endphp

    <article class="rounded border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">Booking #{{ $booking->id }}</h1>
                @if ($booking->parkingLot)
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        <a href="{{ route('parking-lots.show', $booking->parkingLot) }}" class="underline">{{ $booking->parkingLot->name }}</a>
                    </p>
                @else
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lot removed</p>
                @endif
            </div>
            <span class="inline-flex rounded px-2 py-1 text-xs font-medium {{ $statusStyles }}">
                {{ ucfirst($booking->status->value) }}
            </span>
        </div>

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Start</dt>
                <dd class="mt-1 text-sm font-medium">{{ $booking->start_time->format('M j, Y g:i A') }}</dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">End</dt>
                <dd class="mt-1 text-sm font-medium">{{ $booking->end_time->format('M j, Y g:i A') }}</dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Duration</dt>
                <dd class="mt-1 text-sm font-medium">{{ number_format($booking->hours(), 1) }} hours</dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total cost</dt>
                <dd class="mt-1 text-sm font-medium">৳{{ number_format($booking->totalCost(), 2) }}</dd>
            </div>
        </dl>

        @if ($booking->isCancellable())
            <form method="POST" action="{{ route('driver.bookings.cancel', $booking) }}" class="mt-6"
                onsubmit="return confirm('Cancel this booking? The spot will be released.');">
                @csrf
                <button type="submit" class="rounded border border-rose-300 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-200 dark:hover:bg-rose-950">
                    Cancel booking
                </button>
            </form>
        @elseif ($booking->status->value === 'cancelled')
            <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">This booking has been cancelled.</p>
        @else
            <p class="mt-6 text-sm text-slate-500 dark:text-slate-400">This booking can no longer be cancelled because it has already started.</p>
        @endif

        @php
            $canReview = $booking->status->value === 'completed'
                && $booking->parkingLot !== null
                && ! auth()->user()?->reviews()->where('parking_lot_id', $booking->parking_lot_id)->exists();
        @endphp

        @if ($canReview)
            <div class="mt-6 rounded border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">
                <p class="text-sm font-medium text-amber-900 dark:text-amber-100">
                    How was your experience at {{ $booking->parkingLot->name }}?
                </p>
                <a href="{{ route('driver.reviews.create', $booking->parkingLot) }}"
                    class="mt-3 inline-flex rounded border border-amber-300 px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100 dark:border-amber-700 dark:text-amber-100 dark:hover:bg-amber-900">
                    Leave a review
                </a>
            </div>
        @endif
    </article>
</x-layout>