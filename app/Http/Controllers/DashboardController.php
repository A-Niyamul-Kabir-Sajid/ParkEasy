<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Services\OwnerRevenueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly OwnerRevenueService $revenue) {}

    public function admin(): View
    {
        return view('dashboard.admin');
    }

    public function owner(Request $request): View
    {
        $snapshot = $this->revenue->snapshotFor($request->user());

        return view('dashboard.owner', [
            'revenue' => $snapshot,
        ]);
    }

    public function driver(): View
    {
        return view('dashboard.driver');
    }

    /**
     * Redirect the authenticated user to the dashboard matching their role.
     */
    public function redirectForRole(Request $request): RedirectResponse
    {
        $user = $request->user();

        $role = $user?->role instanceof UserRole
            ? $user->role
            : UserRole::Driver;

        return redirect()->route($role->dashboardRouteName());
    }
}
