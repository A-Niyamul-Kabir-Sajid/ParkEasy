<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkingLot extends Model
{
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
    ];
}
