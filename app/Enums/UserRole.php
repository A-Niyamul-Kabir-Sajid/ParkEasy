<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Driver = 'driver';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }

    public function dashboardRouteName(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Owner => 'owner.dashboard',
            self::Driver => 'driver.dashboard',
        };
    }
}
