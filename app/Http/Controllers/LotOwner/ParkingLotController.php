<?php

namespace App\Http\Controllers\LotOwner;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use Illuminate\Http\Request;

class ParkingLotController extends Controller
{
    public function create()
    {
        return view('lotowner.parking_lots.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'hourly_rate' => 'required',
            'total_capacity' => 'required',
        ]);

        ParkingLot::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'hourly_rate' => $request->hourly_rate,
            'total_capacity' => $request->total_capacity,
            'available_spots' => $request->total_capacity,
            'verification_status' => 'pending',
        ]);

        return redirect()->back()
            ->with('success', 'Parking lot created');
    }
}
