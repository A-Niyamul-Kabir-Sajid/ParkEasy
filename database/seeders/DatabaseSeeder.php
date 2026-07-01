<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\Review;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with a realistic demo dataset:
     *
     *  - 1 admin
     *  - 2 owners (each with at least 1 verified lot + 1 pending lot)
     *  - 3 drivers (one of whom has a completed booking + 5-star review,
     *    another with an upcoming active booking, the third is a clean account)
     *
     * Every password is `password`. Every account's email is `*@parkeasy.test`
     * so reviewers can log in without colliding with real addresses.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = $this->seedAdmin();
            [$ownerA, $ownerB] = $this->seedOwners();
            [$primaryDriver, $upcomingDriver, $browseDriver] = $this->seedDrivers();

            $lotA = $this->seedLotsFor($ownerA, verified: true, pendingExtra: 1);
            $lotB = $this->seedLotsFor($ownerB, verified: true, pendingExtra: 0);

            $this->seedCompletedBookingWithReview($primaryDriver, $lotA);
            $this->seedUpcomingBooking($upcomingDriver, $lotB);
            $this->seedBrowseOnly($browseDriver, $lotA);
        });
    }

    private function seedAdmin(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'admin@parkeasy.test'],
            [
                'name' => 'ParkEasy Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
                'phone' => '+8801700000001',
            ],
        );
    }

    /**
     * @return array{0: User, 1: User}
     */
    private function seedOwners(): array
    {
        $ownerA = User::query()->updateOrCreate(
            ['email' => 'owner.aisha@parkeasy.test'],
            [
                'name' => 'Aisha Rahman',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'email_verified_at' => now(),
                'phone' => '+8801700000010',
            ],
        );

        $ownerB = User::query()->updateOrCreate(
            ['email' => 'owner.tanvir@parkeasy.test'],
            [
                'name' => 'Tanvir Hossain',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'email_verified_at' => now(),
                'phone' => '+8801700000011',
            ],
        );

        return [$ownerA, $ownerB];
    }

    /**
     * @return array{0: User, 1: User, 2: User}
     */
    private function seedDrivers(): array
    {
        $primary = User::query()->updateOrCreate(
            ['email' => 'driver.rumi@parkeasy.test'],
            [
                'name' => 'Rumi Akter',
                'password' => Hash::make('password'),
                'role' => UserRole::Driver,
                'email_verified_at' => now(),
                'phone' => '+8801700000020',
            ],
        );

        $upcoming = User::query()->updateOrCreate(
            ['email' => 'driver.sabbir@parkeasy.test'],
            [
                'name' => 'Sabbir Khan',
                'password' => Hash::make('password'),
                'role' => UserRole::Driver,
                'email_verified_at' => now(),
                'phone' => '+8801700000021',
            ],
        );

        $browse = User::query()->updateOrCreate(
            ['email' => 'driver.mehzabin@parkeasy.test'],
            [
                'name' => 'Mehzabin Sultana',
                'password' => Hash::make('password'),
                'role' => UserRole::Driver,
                'email_verified_at' => now(),
                'phone' => '+8801700000022',
            ],
        );

        return [$primary, $upcoming, $browse];
    }

    private function seedLotsFor(User $owner, bool $verified, int $pendingExtra): ParkingLot
    {
        $verifiedLot = ParkingLot::query()
            ->whereBelongsTo($owner, 'owner')
            ->where('verification_status', 'verified')
            ->where('name', $owner->name.' Verified Plaza')
            ->first();

        if ($verifiedLot === null) {
            $verifiedLot = ParkingLot::factory()
                ->forOwner($owner)
                ->verified()
                ->create([
                    'name' => $owner->name.' Verified Plaza',
                    'description' => 'Verified demo lot owned by '.$owner->name,
                    'latitude' => '23.7806',
                    'longitude' => '90.4074',
                    'hourly_rate' => '60.00',
                    'total_capacity' => 40,
                    'available_spots' => 40,
                ]);
        }

        for ($i = 0; $i < $pendingExtra; $i++) {
            $existing = ParkingLot::query()
                ->whereBelongsTo($owner, 'owner')
                ->where('name', $owner->name.' Lot #'.($i + 2).' (pending)')
                ->first();

            if ($existing === null) {
                ParkingLot::factory()
                    ->forOwner($owner)
                    ->create([
                        'name' => $owner->name.' Lot #'.($i + 2).' (pending)',
                        'description' => 'Awaiting admin verification.',
                        'total_capacity' => 20,
                        'available_spots' => 20,
                    ]);
            }
        }

        return $verifiedLot;
    }

    private function seedCompletedBookingWithReview(User $driver, ParkingLot $lot): void
    {
        $start = CarbonImmutable::now()->subDays(3)->setTime(10, 0);
        $end = $start->addHours(3);

        $booking = Booking::query()->updateOrCreate(
            [
                'driver_id' => $driver->id,
                'parking_lot_id' => $lot->id,
                'start_time' => $start,
            ],
            [
                'end_time' => $end,
                'status' => BookingStatus::Completed,
            ],
        );

        if ($booking->wasRecentlyCreated) {
            $lot->decrement('available_spots');
        }

        Review::query()->updateOrCreate(
            [
                'driver_id' => $driver->id,
                'parking_lot_id' => $lot->id,
            ],
            [
                'rating' => 5,
                'comment' => 'Smooth entry, plenty of space, friendly guard.',
            ],
        );
    }

    private function seedUpcomingBooking(User $driver, ParkingLot $lot): void
    {
        $start = CarbonImmutable::now()->addDays(2)->setTime(14, 0);

        $booking = Booking::query()->updateOrCreate(
            [
                'driver_id' => $driver->id,
                'parking_lot_id' => $lot->id,
                'start_time' => $start,
            ],
            [
                'end_time' => $start->addHours(2),
                'status' => BookingStatus::Active,
            ],
        );

        if ($booking->wasRecentlyCreated) {
            $lot->decrement('available_spots');
        }
    }

    private function seedBrowseOnly(User $driver, ParkingLot $lot): void
    {
        // Browse-only driver is intentionally given no booking/review yet so the
        // public browse page still has at least one verified lot to render for
        // a logged-in driver with an empty history.
        unset($driver, $lot);
    }
}
