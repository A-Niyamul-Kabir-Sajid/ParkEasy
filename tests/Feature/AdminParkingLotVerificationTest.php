<?php

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use App\Models\User;
use App\Notifications\ParkingLotRejectedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

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
        ->post(route('admin.verification.reject', $lot), ['reason' => 'Missing ownership documents.'])
        ->assertRedirect(route('admin.verification.index'))
        ->assertSessionHas('status');

    $fresh = $lot->fresh();

    expect($fresh->verification_status)->toBe(ParkingLotVerificationStatus::Rejected);
    expect($fresh->rejection_reason)->toBe('Missing ownership documents.');
});

it('persists the rejection reason and emails the owner', function (): void {
    Notification::fake();

    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create(['name' => 'Downtown Hangar']);

    $this->actingAs($admin)
        ->post(route('admin.verification.reject', $lot), [
            'reason' => 'Please attach photos of the gate and entrance signage.',
        ])
        ->assertRedirect(route('admin.verification.index'));

    $fresh = $lot->fresh();

    expect($fresh->verification_status)->toBe(ParkingLotVerificationStatus::Rejected);
    expect($fresh->rejection_reason)->toBe('Please attach photos of the gate and entrance signage.');

    Notification::assertSentTo($owner, ParkingLotRejectedNotification::class);
});

it('requires a rejection reason', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($admin)
        ->from(route('admin.verification.index'))
        ->post(route('admin.verification.reject', $lot), [])
        ->assertRedirect(route('admin.verification.index'))
        ->assertSessionHasErrors('reason');

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Pending);
});

it('rejects rejection reasons that are too short', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    $lot = ParkingLot::factory()->forOwner($owner)->create();

    $this->actingAs($admin)
        ->from(route('admin.verification.index'))
        ->post(route('admin.verification.reject', $lot), ['reason' => 'no'])
        ->assertRedirect(route('admin.verification.index'))
        ->assertSessionHasErrors('reason');

    expect($lot->fresh()->verification_status)->toBe(ParkingLotVerificationStatus::Pending);
});

it('surfaces the rejection reason on the recent decisions table', function (): void {
    $admin = User::factory()->asAdmin()->create();
    $owner = User::factory()->asOwner()->create();
    ParkingLot::factory()->forOwner($owner)->rejected()->create([
        'name' => 'Corner Garage',
        'rejection_reason' => 'Capacity does not match the photos provided.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.verification.index'))
        ->assertOk()
        ->assertSee('Corner Garage')
        ->assertSee('Capacity does not match the photos provided.')
        ->assertSee('Reason');
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
        ->post(route('admin.verification.reject', $lot), ['reason' => 'Trying again.'])
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
        ->post(route('admin.verification.reject', $lot), ['reason' => 'Should not work.'])
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
