<?php

namespace App\Http\Controllers\Driver;

use App\Enums\ParkingLotVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Models\ParkingLot;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function create(Request $request, ParkingLot $parking_lot): View
    {
        abort_unless($parking_lot->verification_status === ParkingLotVerificationStatus::Verified, 404);

        $this->authorize('create', [Review::class, $parking_lot]);

        return view('driver.reviews.create', [
            'parkingLot' => $parking_lot,
        ]);
    }

    public function store(StoreReviewRequest $request, ParkingLot $parking_lot): RedirectResponse
    {
        abort_unless($parking_lot->verification_status === ParkingLotVerificationStatus::Verified, 404);

        $this->authorize('create', [Review::class, $parking_lot]);

        $validated = $request->validated();

        Review::query()->create([
            'driver_id' => $request->user()->id,
            'parking_lot_id' => $parking_lot->id,
            'rating' => (int) $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return to_route('parking-lots.show', $parking_lot)
            ->with('status', 'Thanks for your review!');
    }
}
