<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ParkingLotVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectParkingLotRequest;
use App\Models\ParkingLot;
use App\Notifications\ParkingLotRejectedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParkingLotVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $pending = ParkingLot::query()
            ->with('owner')
            ->where('verification_status', ParkingLotVerificationStatus::Pending->value)
            ->latest()
            ->get();

        $recent = ParkingLot::query()
            ->with('owner')
            ->whereIn('verification_status', [
                ParkingLotVerificationStatus::Verified->value,
                ParkingLotVerificationStatus::Rejected->value,
            ])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.verification.index', [
            'pending' => $pending,
            'recent' => $recent,
        ]);
    }

    public function approve(Request $request, ParkingLot $parking_lot): RedirectResponse
    {
        $this->authorize('verify', $parking_lot);

        if ($parking_lot->verification_status !== ParkingLotVerificationStatus::Pending) {
            return redirect()
                ->route('admin.verification.index')
                ->with('status', 'Only pending lots can be approved.');
        }

        $parking_lot->update([
            'verification_status' => ParkingLotVerificationStatus::Verified,
        ]);

        return redirect()
            ->route('admin.verification.index')
            ->with('status', 'Parking lot approved.');
    }

    public function reject(RejectParkingLotRequest $request, ParkingLot $parking_lot): RedirectResponse
    {
        $this->authorize('verify', $parking_lot);

        if ($parking_lot->verification_status !== ParkingLotVerificationStatus::Pending) {
            return redirect()
                ->route('admin.verification.index')
                ->with('status', 'Only pending lots can be rejected.');
        }

        $reason = $request->validated()['reason'];

        $parking_lot->update([
            'verification_status' => ParkingLotVerificationStatus::Rejected,
            'rejection_reason' => $reason,
        ]);

        if ($parking_lot->owner !== null) {
            $parking_lot->owner->notify(new ParkingLotRejectedNotification($parking_lot, $reason));
        }

        return redirect()
            ->route('admin.verification.index')
            ->with('status', 'Parking lot rejected.');
    }
}
