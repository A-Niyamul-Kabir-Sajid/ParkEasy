<?php

namespace App\Http\Controllers\LotOwner;

use App\Enums\ParkingLotVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreParkingLotRequest;
use App\Http\Requests\UpdateParkingLotRequest;
use App\Models\ParkingLot;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingLotController extends Controller
{
    public function index(Request $request): View
    {
        $lots = ParkingLot::query()
            ->with('owner')
            ->ownedBy($request->user())
            ->latest()
            ->get();

        return view('owner.parking-lots.index', compact('lots'));
    }

    public function create(): View
    {
        return view('owner.parking-lots.create');
    }

    public function store(StoreParkingLotRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['owner_id'] = $request->user()->id;
        $data['verification_status'] = ParkingLotVerificationStatus::Pending->value;

        $lot = ParkingLot::create($data);

        return redirect()
            ->route('owner.parking-lots.index')
            ->with('status', 'Parking lot submitted for verification.');
    }

    public function edit(Request $request, ParkingLot $parking_lot): View
    {
        $this->authorize('update', $parking_lot);

        $lot = $parking_lot;

        return view('owner.parking-lots.edit', compact('lot'));
    }

    public function update(UpdateParkingLotRequest $request, ParkingLot $parking_lot): RedirectResponse
    {
        $this->authorize('update', $parking_lot);

        $parking_lot->update($request->validated());

        return redirect()
            ->route('owner.parking-lots.index')
            ->with('status', 'Parking lot updated.');
    }

    public function destroy(Request $request, ParkingLot $parking_lot): RedirectResponse
    {
        $this->authorize('delete', $parking_lot);

        $parking_lot->delete();

        return redirect()
            ->route('owner.parking-lots.index')
            ->with('status', 'Parking lot deleted.');
    }
}
