<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PatientAccount;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientAuthController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('Auth/PatientLogin', [
            'samplePhone' => '+15551234567',
        ]);
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|min:7',
        ]);

        $account = PatientAccount::firstOrCreate(
            ['phone_number' => $request->phone_number],
            ['is_verified' => false]
        );

        $otp = '123456'; // Standard OTP for testing/production SMS gateway placeholder
        $account->otp_code = $otp;
        $account->otp_expires_at = now()->addMinutes(15);
        $account->save();

        AuditLogger::log('REQUEST_OTP', 'PatientAccount', $account->id, ['phone' => $account->phone_number]);

        return back()->with('info', "Verification code sent to {$account->phone_number}. (Demo OTP: 123456)");
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp_code' => 'required|string',
        ]);

        $account = PatientAccount::where('phone_number', $request->phone_number)->first();

        if (!$account) {
            return back()->withErrors(['phone_number' => 'Account not found with this phone number.']);
        }

        // Accept demo OTP '123456' or matching stored OTP
        if ($request->otp_code === '123456' || ($account->otp_code && $account->otp_code === $request->otp_code)) {
            $account->is_verified = true;
            $account->otp_code = null;
            $account->save();

            // Store in session
            session()->put('patient_account_id', $account->id);
            session()->put('patient_phone', $account->phone_number);

            AuditLogger::log('PATIENT_LOGIN', 'PatientAccount', $account->id);

            return redirect()->route('patient.dashboard')->with('success', 'Welcome to your Patient Family Portal!');
        }

        return back()->withErrors(['otp_code' => 'Invalid or expired OTP code. Use 123456 for demo.']);
    }

    public function logout(Request $request)
    {
        $accountId = session()->get('patient_account_id');
        if ($accountId) {
            AuditLogger::log('PATIENT_LOGOUT', 'PatientAccount', $accountId);
        }

        session()->forget(['patient_account_id', 'patient_phone', 'active_patient_id']);

        return redirect()->route('patient.login')->with('info', 'You have been logged out of your patient portal.');
    }
}
