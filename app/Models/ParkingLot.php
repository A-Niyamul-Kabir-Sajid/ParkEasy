<?php

namespace App\Models;

use App\Enums\ParkingLotVerificationStatus;
use Database\Factories\ParkingLotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $owner_id
 * @property string $name
 * @property string|null $description
 * @property string $latitude
 * @property string $longitude
 * @property string $hourly_rate
 * @property int $total_capacity
 * @property int $available_spots
 * @property ParkingLotVerificationStatus $verification_status
 */
class ParkingLot extends Model
{
    /** @use HasFactory<ParkingLotFactory> */
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'latitude',
        'longitude',
        'hourly_rate',
        'total_capacity',
        'available_spots',
        'verification_status',
        'rejection_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'hourly_rate' => 'decimal:2',
            'verification_status' => ParkingLotVerificationStatus::class,
        ];
    }

    protected $attributes = [
        'verification_status' => 'pending',
        'available_spots' => 0,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @param  Builder<ParkingLot>  $query
     * @return Builder<ParkingLot>
     */
    public function scopeOwnedBy(Builder $query, User $owner): Builder
    {
        return $query->whereBelongsTo($owner, 'owner');
    }

    public function isPending(): bool
    {
        return $this->verification_status === ParkingLotVerificationStatus::Pending;
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->avg('rating');

        return $avg === null ? null : round((float) $avg, 2);
    }

    public function reviewCount(): int
    {
        return (int) $this->reviews()->count();
    }
}
