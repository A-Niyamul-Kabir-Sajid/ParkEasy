<?php

namespace App\Http\Controllers\Driver;

use App\Enums\BookingStatus;
use App\Enums\ParkingLotVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Notifications\BookingConfirmedNotification;
use App\Notifications\BookingReceivedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function create(Request $request, ParkingLot $parking_lot): View
    {
        abort_unless($parking_lot->verification_status === ParkingLotVerificationStatus::Verified, 404);

        if ((int) $parking_lot->available_spots < 1) {
            return view('driver.bookings.unavailable', [
                'parkingLot' => $parking_lot,
            ]);
        }

        return view('driver.bookings.create', [
            'parkingLot' => $parking_lot,
            'minStart' => CarbonImmutable::now()->addMinutes(15)->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(StoreBookingRequest $request, ParkingLot $parking_lot): RedirectResponse
    {
        abort_unless($parking_lot->verification_status === ParkingLotVerificationStatus::Verified, 404);

        $validated = $request->validated();
        $start = CarbonImmutable::parse($validated['start_time']);
        $end = $start->addHours((int) $validated['duration_hours']);

        $booking = DB::transaction(function () use ($request, $parking_lot, $start, $end): Booking {
            $lot = ParkingLot::query()
                ->whereKey($parking_lot->id)
                ->lockForUpdate()
                ->first();

            abort_if($lot === null, 404);
            abort_unless($lot->verification_status === ParkingLotVerificationStatus::Verified, 404);
            abort_if((int) $lot->available_spots < 1, 409, 'No spots available for this lot.');

            $booking = Booking::query()->create([
                'driver_id' => $request->user()->id,
                'parking_lot_id' => $lot->id,
                'start_time' => $start,
                'end_time' => $end,
                'status' => BookingStatus::Active,
            ]);

            $lot->decrement('available_spots');

            return $booking;
        });

        $booking->loadMissing('parkingLot', 'driver');

        $request->user()->notify(new BookingConfirmedNotification($booking));

        $owner = $booking->parkingLot?->owner;
        if ($owner !== null) {
            $owner->notify(new BookingReceivedNotification($booking));
        }

        return to_route('driver.bookings.show', $booking)
            ->with('status', 'Booking confirmed.');
    }

    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with('parkingLot')
            ->forDriver($request->user())
            ->latest('start_time')
            ->paginate(10);

        return view('driver.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorize('view', $booking);

        $booking->loadMissing('parkingLot', 'driver');

        return view('driver.bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        DB::transaction(function () use ($booking): void {
            $locked = Booking::query()->whereKey($booking->id)->lockForUpdate()->first();
            abort_if($locked === null, 404);

            $locked->update(['status' => BookingStatus::Cancelled]);

            $locked->parkingLot()->lockForUpdate()->first()?->increment('available_spots');
        });

        return to_route('driver.bookings.show', $booking)
            ->with('status', 'Booking cancelled.');
    }
}
