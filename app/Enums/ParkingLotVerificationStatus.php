<?php

namespace App\Enums;

enum ParkingLotVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
