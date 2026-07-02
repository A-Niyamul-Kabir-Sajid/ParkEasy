<?php

use App\Enums\BookingStatus;
use App\Enums\ParkingLotVerificationStatus;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('blocks guests from the booking form', function (): void {
    $lot = ParkingLot::factory()->verified()->create();

    $this->get(route('driver.bookings.create', $lot))->assertRedirect(route('login'));
    $this->post(route('driver.bookings.store', $lot), [])->assertRedirect(route('login'));
});

it('blocks owners and admins from creating a booking', function (): void {
    $lot = ParkingLot::factory()->verified()->create();
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->get(route('driver.bookings.create', $lot))
        ->assertForbidden();

    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)
        ->get(route('driver.bookings.create', $lot))
        ->assertForbidden();
});

it('returns 404 when a driver tries to book an unverified lot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $pending = ParkingLot::factory()->create(['verification_status' => ParkingLotVerificationStatus::Pending->value]);
    $rejected = ParkingLot::factory()->create(['verification_status' => ParkingLotVerificationStatus::Rejected->value]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.create', $pending))
        ->assertNotFound();

    $this->actingAs($driver)
        ->get(route('driver.bookings.create', $rejected))
        ->assertNotFound();
});

it('renders the booking form for a driver on a verified lot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['name' => 'Bookable Spot']);

    $this->actingAs($driver)
        ->get(route('driver.bookings.create', $lot))
        ->assertOk()
        ->assertSee('Book a spot')
        ->assertSee('Bookable Spot');
});

it('shows the unavailable page when no spots remain', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['total_capacity' => 5, 'available_spots' => 0]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.create', $lot))
        ->assertOk()
        ->assertSee('No spots available right now');
});

it('creates a booking and decrements available spots', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['total_capacity' => 5, 'available_spots' => 5, 'hourly_rate' => 50]);

    $start = Carbon::now()->addHours(2)->format('Y-m-d\TH:i');

    $this->actingAs($driver)
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => $start,
            'duration_hours' => 3,
        ])
        ->assertRedirect();

    $booking = Booking::query()->where('driver_id', $driver->id)->first();

    expect($booking)->not->toBeNull();
    expect($booking->status)->toBe(BookingStatus::Active);
    expect($booking->hours())->toBe(3.0);
    expect($booking->totalCost())->toBe(150.0);

    $lot->refresh();

    expect((int) $lot->available_spots)->toBe(4);
});

it('sends a booking confirmation notification to the driver', function (): void {
    Notification::fake();

    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['total_capacity' => 5, 'available_spots' => 5, 'hourly_rate' => 50]);

    $start = Carbon::now()->addHours(2)->format('Y-m-d\TH:i');

    $this->actingAs($driver)
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => $start,
            'duration_hours' => 2,
        ])
        ->assertRedirect();

    Notification::assertSentTo(
        $driver,
        BookingConfirmedNotification::class,
        function (BookingConfirmedNotification $notification) use ($driver): bool {
            return $notification->booking->driver_id === $driver->id;
        },
    );
});

it('does not send the notification when booking creation fails', function (): void {
    Notification::fake();

    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->from(route('driver.bookings.create', $lot))
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => Carbon::now()->subHour()->format('Y-m-d\TH:i'),
            'duration_hours' => 2,
        ])
        ->assertSessionHasErrors(['start_time']);

    Notification::assertNothingSent();
});

it('rejects invalid booking input', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->from(route('driver.bookings.create', $lot))
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => 'not-a-date',
            'duration_hours' => 0,
        ])
        ->assertRedirect(route('driver.bookings.create', $lot))
        ->assertSessionHasErrors(['start_time', 'duration_hours']);

    expect(Booking::count())->toBe(0);
});

it('rejects start_time in the past', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->from(route('driver.bookings.create', $lot))
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => Carbon::now()->subHour()->format('Y-m-d\TH:i'),
            'duration_hours' => 2,
        ])
        ->assertSessionHasErrors(['start_time']);

    expect(Booking::count())->toBe(0);
});

it('rejects duration above the 24 hour cap', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->from(route('driver.bookings.create', $lot))
        ->post(route('driver.bookings.store', $lot), [
            'parking_lot_id' => $lot->id,
            'start_time' => Carbon::now()->addHours(2)->format('Y-m-d\TH:i'),
            'duration_hours' => 48,
        ])
        ->assertSessionHasErrors(['duration_hours']);

    expect(Booking::count())->toBe(0);
});

it('lists only the driver\'s bookings on the my-bookings page', function (): void {
    $driver = User::factory()->asDriver()->create();
    $other = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    Booking::factory()->create(['driver_id' => $driver->id, 'parking_lot_id' => $lot->id]);
    Booking::factory()->create(['driver_id' => $other->id, 'parking_lot_id' => $lot->id]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.index'))
        ->assertOk()
        ->assertSee('My bookings');

    expect(Booking::query()->where('driver_id', $driver->id)->count())->toBe(1);
    expect(Booking::query()->where('driver_id', $other->id)->count())->toBe(1);
});

it('shows the booking detail to its driver', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['hourly_rate' => 40]);
    $booking = Booking::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => Carbon::now()->addHours(2),
        'end_time' => Carbon::now()->addHours(4),
    ]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.show', $booking))
        ->assertOk()
        ->assertSee('Booking #'.$booking->id)
        ->assertSee('৳80.00');
});

it('blocks another driver from viewing someone else\'s booking', function (): void {
    $driver = User::factory()->asDriver()->create();
    $other = User::factory()->asDriver()->create();
    $booking = Booking::factory()->create(['driver_id' => $other->id]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.show', $booking))
        ->assertForbidden();
});

it('cancels an active future booking and restores a spot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['total_capacity' => 3, 'available_spots' => 2]);
    $booking = Booking::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => Carbon::now()->addHours(3),
        'end_time' => Carbon::now()->addHours(5),
        'status' => BookingStatus::Active,
    ]);

    $this->actingAs($driver)
        ->post(route('driver.bookings.cancel', $booking))
        ->assertRedirect(route('driver.bookings.show', $booking));

    $booking->refresh();
    $lot->refresh();

    expect($booking->status)->toBe(BookingStatus::Cancelled);
    expect((int) $lot->available_spots)->toBe(3);
});

it('refuses to cancel a booking that has already started', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['total_capacity' => 3, 'available_spots' => 2]);
    $booking = Booking::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => Carbon::now()->subHour(),
        'end_time' => Carbon::now()->addHour(),
        'status' => BookingStatus::Active,
    ]);

    $this->actingAs($driver)
        ->from(route('driver.bookings.show', $booking))
        ->post(route('driver.bookings.cancel', $booking))
        ->assertForbidden();

    $booking->refresh();
    $lot->refresh();

    expect($booking->status)->toBe(BookingStatus::Active);
    expect((int) $lot->available_spots)->toBe(2);
});

it('shows the lot owner their incoming bookings', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create(['name' => 'My Lot']);
    $otherLot = ParkingLot::factory()->verified()->create();
    Booking::factory()->create(['parking_lot_id' => $lot->id]);
    Booking::factory()->create(['parking_lot_id' => $otherLot->id]);

    $this->actingAs($owner)
        ->get(route('owner.parking-lots.bookings', $lot))
        ->assertOk()
        ->assertSee('My Lot');

    expect(Booking::query()->where('parking_lot_id', $lot->id)->count())->toBe(1);
});

it('blocks another owner from viewing someone else\'s lot bookings', function (): void {
    $owner = User::factory()->asOwner()->create();
    $otherOwner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($otherOwner)->verified()->create();

    $this->actingAs($owner)
        ->get(route('owner.parking-lots.bookings', $lot))
        ->assertForbidden();
});

it('blocks non-owner roles from the owner bookings page', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create();
    $driver = User::factory()->asDriver()->create();

    $this->actingAs($driver)
        ->get(route('owner.parking-lots.bookings', $lot))
        ->assertForbidden();
});

it('exposes the bookings link to lot owners on the public lot page', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create();

    $this->actingAs($owner)
        ->get(route('parking-lots.show', $lot))
        ->assertOk()
        ->assertSee('View bookings');
});
