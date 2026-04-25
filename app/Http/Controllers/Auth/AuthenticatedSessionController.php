<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Request an admin to reset password
     */
    public function requestAdminReset(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $user = \App\Models\User::where('username', $request->username)->first();
        $admin = \App\Models\User::where('role', 'admin')->first();

        if ($user && $admin) {
            $category = \App\Models\Category::first();

            \App\Models\TaskLog::create([
                'user_id' => $admin->id,
                'category_id' => $category ? $category->id : null,
                'department' => 'System Auth',
                'staff_helped' => $user->full_name,
                'description' => "User '{$user->username}' has forgotten their password. Please reach out to them and reset it from the Admin Panel.",
                'priority' => 'red',
            ]);
        }

        return back()->with('status', "Password reset requested. An admin has been notified.");
    }
}
