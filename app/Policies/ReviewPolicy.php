<?php

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Determine whether the user can create a review for the given lot.
     */
    public function create(User $user, ParkingLot $lot): bool
    {
        if (! $user->isDriver()) {
            return false;
        }

        if ((int) $lot->owner_id === (int) $user->id) {
            return false;
        }

        $alreadyReviewed = Review::query()
            ->where('driver_id', $user->id)
            ->where('parking_lot_id', $lot->id)
            ->exists();

        if ($alreadyReviewed) {
            return false;
        }

        return Booking::query()
            ->forDriver($user)
            ->where('parking_lot_id', $lot->id)
            ->where('status', BookingStatus::Completed->value)
            ->exists();
    }
}
