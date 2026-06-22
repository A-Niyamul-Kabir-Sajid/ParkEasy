<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isDriver() || $user->isOwner();
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isDriver()) {
            return $booking->driver_id === $user->id;
        }

        if ($user->isOwner()) {
            return $booking->parkingLot?->owner_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create a booking.
     */
    public function create(User $user): bool
    {
        return $user->isDriver();
    }

    /**
     * Determine whether the user can cancel the booking.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        return $this->view($user, $booking) && $booking->isCancellable();
    }
}
