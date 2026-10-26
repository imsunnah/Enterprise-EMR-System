<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Check patient portal session if PATIENT role is permitted
        if (in_array('PATIENT', $roles) && session()->has('patient_account_id')) {
            return $next($request);
        }

        // 2. Check standard authenticated staff user
        $user = $request->user();
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')->with('error', 'Please log in to continue.');
        }

        // 3. Strict PHI enforcement: BILLER & RECEPTIONIST cannot view clinical SOAP notes or prescriptions
        if (in_array($user->role, ['BILLER', 'RECEPTIONIST']) && ($request->is('*encounter*') || $request->is('*prescription*'))) {
            abort(403, 'Access Denied: Billing & Front Desk personnel are strictly prohibited from viewing clinical SOAP notes and prescriptions (PHI Protection Policy).');
        }

        // 4. Role authorization check
        if (!empty($roles) && !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized: Your role does not grant permission to view this resource.');
        }

        return $next($request);
    }
}
