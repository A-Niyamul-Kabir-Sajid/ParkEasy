<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
