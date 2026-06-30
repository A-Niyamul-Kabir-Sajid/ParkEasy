<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin');
    }

    public function owner(): View
    {
        return view('dashboard.owner');
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
