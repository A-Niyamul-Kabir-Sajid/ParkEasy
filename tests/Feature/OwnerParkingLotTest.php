<?php

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('blocks guests from the owner parking-lot index', function (): void {
    $this->get(route('owner.parking-lots.index'))->assertRedirect(route('login'));
});

it('blocks drivers from the owner parking-lot index', function (): void {
    $driver = User::factory()->asDriver()->create();

    $this->actingAs($driver)
        ->get(route('owner.parking-lots.index'))
        ->assertForbidden();
});

it('renders the owner dashboard with their lots', function (): void {
    $owner = User::factory()->asOwner()->create();
    $ownLot = ParkingLot::factory()->forOwner($owner)->create(['name' => 'My Spot']);
    $otherLot = ParkingLot::factory()->create(['name' => 'Other Spot']);

    $this->actingAs($owner)
        ->get(route('owner.dashboard'))
        ->assertOk()
        ->assertSee('My Spot')
        ->assertDontSee('Other Spot');
});

it('lists only the owner lots on the index page', function (): void {
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->create(['name' => 'Owned A']);
    ParkingLot::factory()->create(['name' => 'Foreign B']);

    $this->actingAs($owner)
        ->get(route('owner.parking-lots.index'))
        ->assertOk()
        ->assertSee('Owned A')
        ->assertDontSee('Foreign B');
});

it('renders the create form for owners', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->get(route('owner.parking-lots.create'))
        ->assertOk()
        ->assertSee('List a new parking lot');
});

it('creates a parking lot in pending status', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->post(route('owner.parking-lots.store'), [
            'name' => 'New Lot',
            'description' => 'Convenient downtown spot',
            'latitude' => 23.78,
            'longitude' => 90.41,
            'hourly_rate' => 45.50,
            'total_capacity' => 30,
            'available_spots' => 12,
        ])
        ->assertRedirect(route('owner.parking-lots.index'))
        ->assertSessionHas('status');

    $lot = ParkingLot::query()->where('name', 'New Lot')->first();

    expect($lot)->not->toBeNull();
    expect($lot->owner_id)->toBe($owner->id);
    expect($lot->verification_status)->toBe(ParkingLotVerificationStatus::Pending);
});

it('rejects invalid input when creating a parking lot', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->from(route('owner.parking-lots.create'))
        ->post(route('owner.parking-lots.store'), [
            'name' => '',
            'latitude' => 999,
            'longitude' => -200,
            'hourly_rate' => -1,
            'total_capacity' => 0,
            'available_spots' => -1,
        ])
        ->assertRedirect(route('owner.parking-lots.create'))
        ->assertSessionHasErrors(['name', 'latitude', 'longitude', 'hourly_rate', 'total_capacity', 'available_spots']);

    expect(ParkingLot::count())->toBe(0);
});

it('caps available spots at total capacity when creating', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->post(route('owner.parking-lots.store'), [
            'name' => 'Oversold',
            'latitude' => 23.78,
            'longitude' => 90.41,
            'hourly_rate' => 50,
            'total_capacity' => 5,
            'available_spots' => 99,
        ])
        ->assertRedirect(route('owner.parking-lots.index'));

    $lot = ParkingLot::query()->where('name', 'Oversold')->first();

    expect($lot)->not->toBeNull();
    expect((int) $lot->available_spots)->toBe(5);
});

it('updates the parking lot owned by the user', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create(['name' => 'Old Name']);

    $this->actingAs($owner)
        ->put(route('owner.parking-lots.update', $lot), [
            'name' => 'New Name',
            'description' => 'Updated description',
            'latitude' => 23.79,
            'longitude' => 90.42,
            'hourly_rate' => 55.00,
            'total_capacity' => 40,
            'available_spots' => 20,
        ])
        ->assertRedirect(route('owner.parking-lots.index'));

    $lot->refresh();

    expect($lot->name)->toBe('New Name');
    expect((float) $lot->hourly_rate)->toBe(55.00);
});

it('blocks another owner from editing a parking lot they do not own', function (): void {
    $owner = User::factory()->asOwner()->create();
    $otherOwner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($otherOwner)->create();

    $this->actingAs($owner)
        ->get(route('owner.parking-lots.edit', $lot))
        ->assertForbidden();
});

it('deletes a parking lot owned by the user', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($owner)
        ->delete(route('owner.parking-lots.destroy', $lot))
        ->assertRedirect(route('owner.parking-lots.index'));

    expect(ParkingLot::query()->whereKey($lot->id)->exists())->toBeFalse();
});

it('blocks another owner from deleting a parking lot they do not own', function (): void {
    $owner = User::factory()->asOwner()->create();
    $otherOwner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($otherOwner)->create();

    $this->actingAs($owner)
        ->delete(route('owner.parking-lots.destroy', $lot))
        ->assertForbidden();

    expect(ParkingLot::query()->whereKey($lot->id)->exists())->toBeTrue();
});
