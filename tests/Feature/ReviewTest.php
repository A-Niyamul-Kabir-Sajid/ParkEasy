<?php

use App\Enums\BookingStatus;
use App\Enums\ParkingLotVerificationStatus;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('blocks guests from the review form', function (): void {
    $lot = ParkingLot::factory()->verified()->create();

    $this->get(route('driver.reviews.create', $lot))->assertRedirect(route('login'));
    $this->post(route('driver.reviews.store', $lot), [
        'parking_lot_id' => $lot->id,
        'rating' => 5,
    ])->assertRedirect(route('login'));
});

it('blocks owners and admins from creating a review', function (): void {
    $lot = ParkingLot::factory()->verified()->create();

    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();

    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($admin)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();
});

it('returns 404 when a driver tries to review an unverified lot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $pending = ParkingLot::factory()->create(['verification_status' => ParkingLotVerificationStatus::Pending->value]);
    $rejected = ParkingLot::factory()->create(['verification_status' => ParkingLotVerificationStatus::Rejected->value]);

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $pending))
        ->assertNotFound();

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $rejected))
        ->assertNotFound();
});

it('forbids a driver with no completed booking at the lot from reviewing', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();

    Booking::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'status' => BookingStatus::Active,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();
});

it('forbids a driver from reviewing their own lot', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create();

    Booking::factory()->completed()->create([
        'driver_id' => $owner->id,
        'parking_lot_id' => $lot->id,
    ]);

    $this->actingAs($owner)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 5,
        ])
        ->assertForbidden();
});

it('renders the review form for a driver who completed a booking', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['name' => 'Reviewable Lot']);

    Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $lot))
        ->assertOk()
        ->assertSee('Leave a review')
        ->assertSee('Reviewable Lot');
});

it('creates a review and shows it on the public lot page', function (): void {
    $driver = User::factory()->asDriver()->create(['name' => 'Happy Driver']);
    $lot = ParkingLot::factory()->verified()->create(['name' => 'Prime Spot']);

    Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    $this->actingAs($driver)
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 5,
            'comment' => 'Excellent service and easy access.',
        ])
        ->assertRedirect(route('parking-lots.show', $lot));

    $review = Review::query()->where('driver_id', $driver->id)->first();

    expect($review)->not->toBeNull();
    expect((int) $review->rating)->toBe(5);
    expect($review->comment)->toBe('Excellent service and easy access.');

    $this->get(route('parking-lots.show', $lot))
        ->assertOk()
        ->assertSee('5.0')
        ->assertSee('(1 review)')
        ->assertSee('Recent reviews')
        ->assertSee('Excellent service and easy access.')
        ->assertSee('Happy Driver');
});

it('computes the average rating and review count across multiple reviews', function (): void {
    $driverA = User::factory()->asDriver()->create();
    $driverB = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    Review::factory()->create([
        'driver_id' => $driverA->id,
        'parking_lot_id' => $lot->id,
        'rating' => 5,
    ]);
    Review::factory()->create([
        'driver_id' => $driverB->id,
        'parking_lot_id' => $lot->id,
        'rating' => 3,
    ]);

    $lot->refresh();

    expect($lot->reviewCount())->toBe(2);
    expect($lot->averageRating())->toBe(4.0);
});

it('forbids a driver from submitting a second review for the same lot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    Review::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'rating' => 4,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.reviews.create', $lot))
        ->assertForbidden();

    $this->actingAs($driver)
        ->from(route('driver.reviews.create', $lot))
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 5,
            'comment' => 'Trying again',
        ])
        ->assertSessionHasErrors('parking_lot_id');

    expect(Review::query()->where('driver_id', $driver->id)->count())->toBe(1);
});

it('validates the rating range', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    $this->actingAs($driver)
        ->from(route('driver.reviews.create', $lot))
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 9,
        ])
        ->assertSessionHasErrors('rating');

    $this->actingAs($driver)
        ->from(route('driver.reviews.create', $lot))
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 0,
        ])
        ->assertSessionHasErrors('rating');

    expect(Review::count())->toBe(0);
});

it('rejects a review when the driver has not completed a booking', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($driver)
        ->from(route('driver.reviews.create', $lot))
        ->post(route('driver.reviews.store', $lot), [
            'parking_lot_id' => $lot->id,
            'rating' => 4,
            'comment' => 'Should not be saved.',
        ])
        ->assertSessionHasErrors('parking_lot_id');

    expect(Review::count())->toBe(0);
});

it('does not show the review CTA on the booking show page when the booking is not completed', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();
    $booking = Booking::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'start_time' => Carbon::now()->addHours(2),
        'end_time' => Carbon::now()->addHours(4),
        'status' => BookingStatus::Active,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.show', $booking))
        ->assertOk()
        ->assertDontSee('Leave a review');
});

it('shows the review CTA on the booking show page when the booking is completed', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();
    $booking = Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.show', $booking))
        ->assertOk()
        ->assertSee('Leave a review')
        ->assertSee(route('driver.reviews.create', $lot));
});

it('hides the review CTA once the driver has already reviewed the lot', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create();
    $booking = Booking::factory()->completed()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
    ]);

    Review::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'rating' => 5,
    ]);

    $this->actingAs($driver)
        ->get(route('driver.bookings.show', $booking))
        ->assertOk()
        ->assertDontSee('Leave a review');
});

it('displays the average rating on the public browse page', function (): void {
    $driver = User::factory()->asDriver()->create();
    $lot = ParkingLot::factory()->verified()->create(['name' => 'Browse Favorite']);

    Review::factory()->create([
        'driver_id' => $driver->id,
        'parking_lot_id' => $lot->id,
        'rating' => 4,
    ]);

    $this->get(route('parking-lots.browse'))
        ->assertOk()
        ->assertSee('Browse Favorite')
        ->assertSee('4.0')
        ->assertSee('(1 review)');
});

it('omits the rating block when the lot has no reviews', function (): void {
    ParkingLot::factory()->verified()->create(['name' => 'Unreviewed Lot']);

    $response = $this->get(route('parking-lots.browse'))->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('Unreviewed Lot');
    expect($content)->not->toContain('(0 reviews)');
    expect($content)->not->toContain('class="mt-2 flex items-center gap-1 text-xs');
});
