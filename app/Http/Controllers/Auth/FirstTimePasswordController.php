<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class FirstTimePasswordController extends Controller
{
    /**
     * Show the first-time password change form.
     */
    public function show()
    {
        // Only allow access if user is authenticated and it's their first login
        if (!Auth::check() || !Auth::user()->is_first_login) {
            return redirect()->route('login');
        }

        return view('auth.first-time-password');
    }

    /**
     * Handle the first-time password change.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validate that it's actually a first-time login
        if (!$user->is_first_login) {
            return redirect()->route('dashboard')->with('error', 'Invalid request.');
        }

        // Validate the request
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        // Verify the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The provided password does not match your current password.'
            ]);
        }

        // Update the user's password and mark as no longer first login
        $user->update([
            'password' => Hash::make($request->password),
            'is_first_login' => false,
            'password_changed_at' => now(),
        ]);

        // Log the user out and back in to refresh the session
        Auth::logout();
        Auth::login($user);

        return redirect('/home')->with('success', 'Password changed successfully! Welcome to the system.');
    }
}
