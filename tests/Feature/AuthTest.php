<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the login page for guests', function (): void {
    $this->get(route('login'))->assertOk()->assertSee('Log in');
});

it('redirects authenticated users away from the login page', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('login'))->assertRedirect(route('dashboard'));
});

it('logs a user in with valid credentials', function (): void {
    $user = User::factory()->asDriver()->create([
        'password' => bcrypt('secret-pw'),
    ]);

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'secret-pw',
    ]);

    $response->assertRedirect(route('driver.dashboard'));
    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function (): void {
    User::factory()->create([
        'password' => bcrypt('secret-pw'),
    ]);

    $this->post(route('login'), [
        'email' => 'nobody@example.com',
        'password' => 'wrong',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('allows a user to log out', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('home'));

    $this->assertGuest();
});

it('renders the registration page with role options', function (): void {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('value="owner"', false)
        ->assertSee('value="driver"', false)
        ->assertSee('List my parking lot')
        ->assertSee('Find parking');
});

it('registers a new owner and logs them in', function (): void {
    $response = $this->post(route('register'), [
        'name' => 'Lot Owner',
        'email' => 'owner@example.com',
        'phone' => '+8801711000000',
        'role' => UserRole::Owner->value,
        'password' => 'secret-pw',
        'password_confirmation' => 'secret-pw',
    ]);

    $response->assertRedirect(route('owner.dashboard'));

    $user = User::query()->where('email', 'owner@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->role)->toBe(UserRole::Owner);

    $this->assertAuthenticatedAs($user);
});

it('registers a new driver and logs them in', function (): void {
    $this->post(route('register'), [
        'name' => 'Driver Person',
        'email' => 'driver@example.com',
        'role' => UserRole::Driver->value,
        'password' => 'secret-pw',
        'password_confirmation' => 'secret-pw',
    ])->assertRedirect(route('driver.dashboard'));

    expect(User::query()->where('email', 'driver@example.com')->firstOrFail()->role)
        ->toBe(UserRole::Driver);
});

it('rejects registration with mismatched passwords', function (): void {
    $this->post(route('register'), [
        'name' => 'Mismatch',
        'email' => 'mismatch@example.com',
        'role' => UserRole::Driver->value,
        'password' => 'secret-pw',
        'password_confirmation' => 'different',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

it('does not allow registering as an admin', function (): void {
    $this->post(route('register'), [
        'name' => 'Sneaky',
        'email' => 'sneaky@example.com',
        'role' => UserRole::Admin->value,
        'password' => 'secret-pw',
        'password_confirmation' => 'secret-pw',
    ])->assertSessionHasErrors('role');

    expect(User::query()->where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

it('redirects the dashboard route to the role-specific dashboard', function (): void {
    $owner = User::factory()->asOwner()->create();
    $driver = User::factory()->asDriver()->create();
    $admin = User::factory()->asAdmin()->create();

    $this->actingAs($owner)->get(route('dashboard'))->assertRedirect(route('owner.dashboard'));
    $this->actingAs($driver)->get(route('dashboard'))->assertRedirect(route('driver.dashboard'));
    $this->actingAs($admin)->get(route('dashboard'))->assertRedirect(route('admin.dashboard'));
});

it('blocks drivers from accessing owner routes', function (): void {
    $driver = User::factory()->asDriver()->create();

    $this->actingAs($driver)
        ->get(route('owner.dashboard'))
        ->assertForbidden();
});

it('blocks owners from accessing admin routes', function (): void {
    $owner = User::factory()->asOwner()->create();

    $this->actingAs($owner)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('blocks unauthenticated users from role dashboards', function (): void {
    $this->get(route('owner.dashboard'))->assertRedirect(route('login'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('driver.dashboard'))->assertRedirect(route('login'));
});
