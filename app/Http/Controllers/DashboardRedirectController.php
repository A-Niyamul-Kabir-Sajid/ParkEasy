<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * Redirect the authenticated user to the dashboard matching their role.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        $role = $user?->role instanceof UserRole
            ? $user->role
            : UserRole::Driver;

        return redirect()->route($role->dashboardRouteName());
    }
}
