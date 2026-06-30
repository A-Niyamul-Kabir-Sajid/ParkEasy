<?php

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('blocks guests from the admin verification queue', function (): void {
    $this->get(route('admin.verification.index'))->assertRedirect(route('login'));
});

it('blocks owners from the admin verification queue', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->get(route('admin.verification.index'))
        ->assertForbidden();
});

it('renders the queue for admins with pending lots', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->create(['name' => 'Awaiting Review']);
    ParkingLot::factory()->verified()->create(['name' => 'Already Verified']);

    $this->actingAs($admin)
        ->get(route('admin.verification.index'))
        ->assertOk()
        ->assertSee('Awaiting Review')
        ->assertSee('Approve')
        ->assertSee('Reject')
        ->assertSee('Already Verified');
});

it('lets an admin approve a pending lot', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($admin)
        ->post(route('admin.verification.approve', $lot))
        ->assertRedirect(route('admin.verification.index'))
        ->assertSessionHas('status');

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Verified);
});

it('lets an admin reject a pending lot', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($admin)
        ->post(route('admin.verification.reject', $lot))
        ->assertRedirect(route('admin.verification.index'))
        ->assertSessionHas('status');

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Rejected);
});

it('refuses to re-approve an already verified lot', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $lot = ParkingLot::factory()->verified()->create();

    $this->actingAs($admin)
        ->post(route('admin.verification.approve', $lot))
        ->assertRedirect(route('admin.verification.index'));

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Verified);
});

it('refuses to re-reject an already rejected lot', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $lot = ParkingLot::factory()->rejected()->create();

    $this->actingAs($admin)
        ->post(route('admin.verification.reject', $lot))
        ->assertRedirect(route('admin.verification.index'));

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Rejected);
});

it('blocks non-admin users from approving or rejecting', function (): void {
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($owner)
        ->post(route('admin.verification.approve', $lot))
        ->assertForbidden();

    $this->actingAs($owner)
        ->post(route('admin.verification.reject', $lot))
        ->assertForbidden();

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Pending);
});

it('shows status counts on the admin dashboard', function (): void {
    $admin = User::factory()->asAdmin()->create();
    ParkingLot::factory()->count(2)->create();
    ParkingLot::factory()->verified()->create();
    ParkingLot::factory()->rejected()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Open verification queue')
        ->assertSeeInOrder([
            'Pending',
            'Verified',
            'Rejected',
        ]);
});
