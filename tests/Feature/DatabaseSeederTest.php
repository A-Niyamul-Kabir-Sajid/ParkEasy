<?php

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds one admin, two owners, and three drivers with deterministic accounts', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('role', UserRole::Admin->value)->count())->toBe(1);
    expect(User::query()->where('role', UserRole::Owner->value)->count())->toBe(2);
    expect(User::query()->where('role', UserRole::Driver->value)->count())->toBe(3);
});

it('seeds at least one verified lot per owner', function (): void {
    $this->seed(DatabaseSeeder::class);

    foreach (User::query()->where('role', UserRole::Owner->value)->get() as $owner) {
        $verifiedCount = ParkingLot::query()
            ->whereBelongsTo($owner, 'owner')
            ->where('verification_status', 'verified')
            ->count();

        expect($verifiedCount)->toBeGreaterThanOrEqual(1);
    }
});

it('creates a completed booking and a 5-star review for the primary driver', function (): void {
    $this->seed(DatabaseSeeder::class);

    $primary = User::query()->where('email', 'driver.rumi@parkeasy.test')->firstOrFail();

    $booking = Booking::query()
        ->whereBelongsTo($primary, 'driver')
        ->where('status', BookingStatus::Completed->value)
        ->first();

    expect($booking)->not->toBeNull();

    $review = Review::query()
        ->whereBelongsTo($primary, 'driver')
        ->first();

    expect($review)->not->toBeNull();
    expect((int) $review->rating)->toBe(5);
});

it('creates an active future booking for the upcoming driver', function (): void {
    $this->seed(DatabaseSeeder::class);

    $upcoming = User::query()->where('email', 'driver.sabbir@parkeasy.test')->firstOrFail();

    $booking = Booking::query()
        ->whereBelongsTo($upcoming, 'driver')
        ->where('status', BookingStatus::Active->value)
        ->first();

    expect($booking)->not->toBeNull();
    expect($booking->start_time->isFuture())->toBeTrue();
});

it('is idempotent and can be re-run without duplicating accounts', function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(1 + 2 + 3);

    $primary = User::query()->where('email', 'driver.rumi@parkeasy.test')->firstOrFail();

    expect(Review::query()->whereBelongsTo($primary, 'driver')->count())->toBe(1);
});

it('produces available_spots counts that are internally consistent for verified lots', function (): void {
    $this->seed(DatabaseSeeder::class);

    ParkingLot::query()
        ->where('verification_status', 'verified')
        ->get()
        ->each(function (ParkingLot $lot): void {
            $booked = Booking::query()
                ->where('parking_lot_id', $lot->id)
                ->where('status', BookingStatus::Active->value)
                ->where('start_time', '>=', now())
                ->count();

            $used = Booking::query()
                ->where('parking_lot_id', $lot->id)
                ->whereIn('status', [
                    BookingStatus::Active->value,
                    BookingStatus::Completed->value,
                ])
                ->where('end_time', '>=', now())
                ->count();

            expect((int) $lot->available_spots)->toBeGreaterThanOrEqual(0);
            expect($booked)->toBeLessThanOrEqual((int) $lot->total_capacity);
            expect($used)->toBeLessThanOrEqual((int) $lot->total_capacity);
        });
});
