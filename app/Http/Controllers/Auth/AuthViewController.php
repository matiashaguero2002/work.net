<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AuthViewController extends Controller
{
    public function showRoleSelection(): View
    {
        return view('auth.role');
    }

    public function showLoginCandidate(): View
    {
        return view('auth.login-candidate');
    }

    public function showLoginEmployer(): View
    {
        return view('auth.login-employer');
    }

    public function showRegisterCandidate(): View
    {
        return view('auth.register-candidate');
    }

    public function showRegisterEmployer(): View
    {
        return view('auth.register-employer');
    }

    public function showDashboardDemo(): View
    {
        return view('auth.dashboard-demo');
    }
}
