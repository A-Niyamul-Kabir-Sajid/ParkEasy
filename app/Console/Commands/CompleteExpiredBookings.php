<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[Signature('bookings:complete-expired')]
#[Description('Mark active bookings as completed once their end time has passed.')]
class CompleteExpiredBookings extends Command
{
    public function handle(): int
    {
        $now = Carbon::now();

        $count = DB::transaction(function () use ($now): int {
            $expired = Booking::query()
                ->active()
                ->where('end_time', '<=', $now)
                ->lockForUpdate()
                ->get();

            if ($expired->isEmpty()) {
                return 0;
            }

            foreach ($expired as $booking) {
                $booking->update(['status' => BookingStatus::Completed]);

                $booking->parkingLot()
                    ->lockForUpdate()
                    ->first()
                    ?->increment('available_spots');
            }

            return $expired->count();
        });

        $this->components->info("Completed {$count} expired booking(s).");

        return self::SUCCESS;
    }
}
