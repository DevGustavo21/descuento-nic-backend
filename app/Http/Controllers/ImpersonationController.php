<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');

        if (! $impersonatorId) {
            return redirect('/negocio');
        }

        $admin = User::query()->find($impersonatorId);

        if ($admin && $admin->canAccessAdmin()) {
            Auth::login($admin);
            Audit::log('impersonation.stop', Auth::user());

            return redirect('/admin/users');
        }

        Auth::logout();

        return redirect('/admin/login');
    }
}
