<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows zero revenue for an owner with no lots', function (): void {
    $owner = User::factory()->asOwner()->create();

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk();
    $response->assertSee('&#2547;0.00', false);
    $response->assertSee('From 0 completed bookings.', false);
    $response->assertSee('Total lots', false);
});

it('sums lifetime revenue from completed bookings across all of the owner lots', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lotA = ParkingLot::factory()->forOwner($owner)->verified()->create(['hourly_rate' => '100.00']);
    $lotB = ParkingLot::factory()->forOwner($owner)->verified()->create(['hourly_rate' => '50.00']);

    // lotA: 2-hour booking → 200.00
    $startA = CarbonImmutable::now()->subDays(2)->setTime(9, 0);
    Booking::query()->create([
        'driver_id' => User::factory()->asDriver()->create()->id,
        'parking_lot_id' => $lotA->id,
        'start_time' => $startA,
        'end_time' => $startA->addHours(2),
        'status' => BookingStatus::Completed,
    ]);

    // lotB: 4-hour booking → 200.00
    $startB = CarbonImmutable::now()->subDay()->setTime(11, 0);
    Booking::query()->create([
        'driver_id' => User::factory()->asDriver()->create()->id,
        'parking_lot_id' => $lotB->id,
        'start_time' => $startB,
        'end_time' => $startB->addHours(4),
        'status' => BookingStatus::Completed,
    ]);

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk();
    $response->assertSee('&#2547;400.00', false);
    $response->assertSee('From 2 completed bookings.', false);
});

it('does not include cancelled or future-active bookings in lifetime revenue', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create(['hourly_rate' => '100.00']);
    $driver = User::factory()->asDriver()->create();

    $cancelledStart = CarbonImmutable::now()->subDay()->setTime(8, 0);
    Booking::query()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => $cancelledStart,
        'end_time' => $cancelledStart->addHours(2),
        'status' => BookingStatus::Cancelled,
    ]);

    $futureStart = CarbonImmutable::now()->addDays(2)->setTime(8, 0);
    Booking::query()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => $futureStart,
        'end_time' => $futureStart->addHours(2),
        'status' => BookingStatus::Active,
    ]);

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk();
    $response->assertSee('&#2547;0.00', false);
    $response->assertSee('From 0 completed bookings.', false);
    $response->assertSee('Upcoming bookings', false);
});

it('reports the count of upcoming active bookings for the owner lots', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create();
    $driver = User::factory()->asDriver()->create();

    foreach (range(1, 3) as $i) {
        $start = CarbonImmutable::now()->addDays($i)->setTime(10, 0);
        Booking::query()->create([
            'driver_id' => $driver->id,
            'parking_lot_id' => $lot->id,
            'start_time' => $start,
            'end_time' => $start->addHour(),
            'status' => BookingStatus::Active,
        ]);
    }

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk();
    $response->assertSee('Upcoming bookings', false);
    $response->assertSeeInOrder([
        'Upcoming bookings',
        '3',
    ], false);
});

it('forbids drivers and admins from viewing the owner dashboard', function (): void {
    $driver = User::factory()->asDriver()->create();
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($driver)->get(route('owner.dashboard'))->assertForbidden();
    $this->actingAs($admin)->get(route('owner.dashboard'))->assertForbidden();
});

it('does not include other owners bookings in the revenue snapshot', function (): void {
    $owner = User::factory()->asOwner()->create();
    $other = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($other)->verified()->create(['hourly_rate' => '100.00']);
    $driver = User::factory()->asDriver()->create();

    $start = CarbonImmutable::now()->subDay()->setTime(8, 0);
    Booking::query()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => $start,
        'end_time' => $start->addHours(5),
        'status' => BookingStatus::Completed,
    ]);

    $response = $this->actingAs($owner)->get(route('owner.dashboard'));

    $response->assertOk();
    $response->assertSee('&#2547;0.00', false);
});
