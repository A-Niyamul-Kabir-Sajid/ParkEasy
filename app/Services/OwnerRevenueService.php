<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OwnerRevenueService
{
    /**
     * Compute the revenue snapshot for an owner across all of their lots.
     *
     * `revenue` is the sum of `totalCost()` for every completed booking that
     * has actually ended (i.e. `end_time <= now`). In-flight bookings are
     * excluded so a partially-used slot doesn't double-count as the booking
     * progresses toward completion.
     *
     * @return array{
     *     lifetime: float,
     *     this_month: float,
     *     completed_bookings: int,
     *     upcoming_bookings: int
     * }
     */
    public function snapshotFor(User $owner, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        $bookings = $this->completedBookingsFor($owner)
            ->with('parkingLot')
            ->get();

        $lifetime = $bookings
            ->filter(fn (Booking $booking): bool => $booking->end_time->lessThanOrEqualTo($now))
            ->sum(fn (Booking $booking): float => $booking->totalCost());

        $startOfMonth = $now->startOfMonth();

        $thisMonth = $bookings
            ->filter(fn (Booking $booking): bool => $booking->end_time->greaterThanOrEqualTo($startOfMonth)
                && $booking->end_time->lessThanOrEqualTo($now))
            ->sum(fn (Booking $booking): float => $booking->totalCost());

        $upcomingBookings = Booking::query()
            ->whereIn('parking_lot_id', $this->lotIdsFor($owner))
            ->where('status', BookingStatus::Active->value)
            ->where('start_time', '>=', $now)
            ->count();

        return [
            'lifetime' => round($lifetime, 2),
            'this_month' => round($thisMonth, 2),
            'completed_bookings' => $bookings->count(),
            'upcoming_bookings' => (int) $upcomingBookings,
        ];
    }

    /**
     * @return Collection<int, int>
     */
    private function lotIdsFor(User $owner): Collection
    {
        return $owner->parkingLots()->pluck('id');
    }

    /**
     * @return Builder<Booking>
     */
    private function completedBookingsFor(User $owner): Builder
    {
        return Booking::query()
            ->whereIn('parking_lot_id', $this->lotIdsFor($owner))
            ->where('status', BookingStatus::Completed->value);
    }
}
