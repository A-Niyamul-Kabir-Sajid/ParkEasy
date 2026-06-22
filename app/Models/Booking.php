<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $driver_id
 * @property int $parking_lot_id
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property BookingStatus $status
 */
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'parking_lot_id',
        'start_time',
        'end_time',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * @return BelongsTo<ParkingLot, $this>
     */
    public function parkingLot(): BelongsTo
    {
        return $this->belongsTo(ParkingLot::class);
    }

    public function hours(): float
    {
        $diff = $this->start_time->diffInMinutes($this->end_time);

        return round($diff / 60, 2);
    }

    public function totalCost(): float
    {
        return round($this->hours() * (float) $this->parkingLot->hourly_rate, 2);
    }

    public function isCancellable(): bool
    {
        return $this->status === BookingStatus::Active && $this->start_time->isFuture();
    }

    /**
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeForDriver(Builder $query, User $driver): Builder
    {
        return $query->whereBelongsTo($driver, 'driver');
    }

    /**
     * @param  Builder<Booking>  $query
     * @return Builder<Booking>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Active->value);
    }
}
