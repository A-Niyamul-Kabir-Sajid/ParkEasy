<?php

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('shows the welcome page to guests', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('Find parking without the hassle')
        ->assertSee('Browse parking lots');
});

it('shows the welcome page to authenticated users with a dashboard link', function (): void {
    $driver = User::factory()->asDriver()->create();

    actingAs($driver)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Go to dashboard');
});

it('lets guests browse parking lots and only shows verified ones', function (): void {
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->verified()->create(['name' => 'Verified Lot A']);
    ParkingLot::factory()->forOwner($owner)->create(['name' => 'Pending Lot B']);
    ParkingLot::factory()->forOwner($owner)->rejected()->create(['name' => 'Rejected Lot C']);

    get(route('parking-lots.browse'))
        ->assertOk()
        ->assertSee('Verified Lot A')
        ->assertDontSee('Pending Lot B')
        ->assertDontSee('Rejected Lot C');
});

it('filters verified parking lots by name when a search term is given', function (): void {
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->verified()->create(['name' => 'Airport Garage']);
    ParkingLot::factory()->forOwner($owner)->verified()->create(['name' => 'City Center Parking']);

    get(route('parking-lots.browse', ['q' => 'Airport']))
        ->assertOk()
        ->assertSee('Airport Garage')
        ->assertDontSee('City Center Parking');
});

it('shows an empty-state message when no verified lot matches the search', function (): void {
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->verified()->create(['name' => 'Airport Garage']);

    get(route('parking-lots.browse', ['q' => 'NowhereVille']))
        ->assertOk()
        ->assertSee('No verified parking lots match');
});

it('shows the detail page for a verified parking lot', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create([
        'name' => 'Airport Garage',
        'hourly_rate' => 75.50,
        'total_capacity' => 100,
        'available_spots' => 42,
    ]);

    get(route('parking-lots.show', $lot))
        ->assertOk()
        ->assertSee('Airport Garage')
        ->assertSee('৳75.50')
        ->assertSee('42 / 100')
        ->assertSee('Verified')
        ->assertSee('Managed by '.$owner->name);
});

it('returns 404 when viewing the detail page of a pending parking lot', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create([
        'name' => 'Pending Garage',
        'verification_status' => ParkingLotVerificationStatus::Pending->value,
    ]);

    get(route('parking-lots.show', $lot))->assertNotFound();
});

it('returns 404 when viewing the detail page of a rejected parking lot', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->rejected()->create([
        'name' => 'Rejected Garage',
    ]);

    get(route('parking-lots.show', $lot))->assertNotFound();
});

it('shows a login prompt for guests on the detail page', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->verified()->create();

    get(route('parking-lots.show', $lot))
        ->assertOk()
        ->assertSee('Log in to book');
});
