<?php

namespace Database\Factories;

use App\Models\ParkingLot;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'driver_id' => User::factory()->asDriver(),
            'parking_lot_id' => ParkingLot::factory()->verified(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.7)->sentence(),
        ];
    }
}
