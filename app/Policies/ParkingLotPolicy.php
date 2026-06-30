<?php

namespace App\Policies;

use App\Models\ParkingLot;
use App\Models\User;

class ParkingLotPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isOwner();
    }

    public function view(User $user, ParkingLot $parkingLot): bool
    {
        return $user->isOwner() && $user->id === $parkingLot->owner_id;
    }

    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    public function update(User $user, ParkingLot $parkingLot): bool
    {
        return $user->isOwner() && $user->id === $parkingLot->owner_id;
    }

    public function delete(User $user, ParkingLot $parkingLot): bool
    {
        return $user->isOwner() && $user->id === $parkingLot->owner_id;
    }

    public function verify(User $user, ParkingLot $parkingLot): bool
    {
        return $user->isAdmin();
    }
}
