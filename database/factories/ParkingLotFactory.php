<?php

namespace Database\Factories;

use App\Enums\ParkingLotVerificationStatus;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParkingLot>
 */
class ParkingLotFactory extends Factory
{
    protected $model = ParkingLot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->asOwner(),
            'name' => fake()->company().' Parking',
            'description' => fake()->sentence(),
            'latitude' => fake()->latitude(23.6, 23.9),
            'longitude' => fake()->longitude(90.3, 90.6),
            'hourly_rate' => fake()->randomFloat(2, 20, 200),
            'total_capacity' => fake()->numberBetween(10, 200),
            'available_spots' => fake()->numberBetween(0, 200),
            'verification_status' => ParkingLotVerificationStatus::Pending->value,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => ParkingLotVerificationStatus::Verified->value,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'verification_status' => ParkingLotVerificationStatus::Rejected->value,
        ]);
    }

    public function forOwner(User $owner): static
    {
        return $this->state(fn (): array => [
            'owner_id' => $owner->id,
        ]);
    }
}
