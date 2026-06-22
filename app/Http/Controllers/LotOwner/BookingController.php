<?php

namespace App\Http\Controllers\LotOwner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request, ParkingLot $parking_lot): View
    {
        abort_unless($parking_lot->owner_id === $request->user()->id, 403);

        $bookings = Booking::query()
            ->with('driver')
            ->where('parking_lot_id', $parking_lot->id)
            ->latest('start_time')
            ->paginate(15);

        return view('owner.parking-lots.bookings', [
            'parkingLot' => $parking_lot,
            'bookings' => $bookings,
            'verificationStatus' => $parking_lot->verification_status,
        ]);
    }
}
