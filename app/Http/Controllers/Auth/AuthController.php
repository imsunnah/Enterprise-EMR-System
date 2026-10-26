<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'staffAccounts' => User::select('id', 'name', 'email', 'role', 'specialization')
                ->where('is_active', true)
                ->get(),
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role === 'DOCTOR') {
                return redirect()->intended(route('doctor.encounters'));
            } elseif ($user->role === 'BILLER') {
                return redirect()->intended(route('billing.index'));
            } elseif ($user->role === 'NURSE' || $user->role === 'ADMIN') {
                return redirect()->intended(route('clerk.queue'));
            }

            return redirect()->intended(route('clerk.queue'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Demo one-click switch for pairing and evaluation.
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:ADMIN,DOCTOR,NURSE,BILLER',
        ]);

        $user = User::where('role', $request->role)->first();
        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            if ($request->role === 'DOCTOR') {
                return redirect()->route('doctor.encounters')->with('success', "Switched role to Dr. {$user->name} (Doctor)");
            } elseif ($request->role === 'BILLER') {
                return redirect()->route('billing.index')->with('success', "Switched role to {$user->name} (Billing Clerk)");
            } elseif ($request->role === 'ADMIN') {
                return redirect()->route('admin.catalogs')->with('success', "Switched role to {$user->name} (Administrator)");
            } else {
                return redirect()->route('clerk.queue')->with('success', "Switched role to {$user->name} ({$request->role})");
            }
        }

        return back()->with('error', 'No user found for role.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
