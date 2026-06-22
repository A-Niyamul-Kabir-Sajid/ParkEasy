<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 weeks');

        return [
            'driver_id' => User::factory()->asDriver(),
            'parking_lot_id' => ParkingLot::factory()->verified(),
            'start_time' => $start,
            'end_time' => (clone $start)->modify('+'.fake()->numberBetween(1, 6).' hours'),
            'status' => BookingStatus::Active->value,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Cancelled->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => BookingStatus::Completed->value,
        ]);
    }
}
