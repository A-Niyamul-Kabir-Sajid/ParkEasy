<?php

namespace App\Http\Controllers\Browse;

use App\Enums\ParkingLotVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ParkingLotController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $lots = ParkingLot::query()
            ->with('owner')
            ->where('verification_status', ParkingLotVerificationStatus::Verified->value)
            ->when($search !== '', fn ($query) => $query->where('name', 'ilike', '%'.$search.'%'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('public.parking-lots.index', [
            'lots' => $lots,
            'search' => $search,
        ]);
    }

    public function show(ParkingLot $parking_lot): View
    {
        abort_unless(
            $parking_lot->verification_status === ParkingLotVerificationStatus::Verified,
            404
        );

        $parking_lot->load('owner');

        return view('public.parking-lots.show', [
            'lot' => $parking_lot,
        ]);
    }
}
