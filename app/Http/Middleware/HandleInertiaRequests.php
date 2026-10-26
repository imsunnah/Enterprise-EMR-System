<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $patientAccountId = session()->get('patient_account_id');
        $patientAccountData = null;

        if ($patientAccountId && class_exists(\App\Models\PatientAccount::class)) {
            $account = \App\Models\PatientAccount::with('patients')->find($patientAccountId);
            if ($account) {
                $patientAccountData = [
                    'id' => $account->id,
                    'phone_number' => $account->phone_number,
                    'patients' => $account->patients,
                    'active_patient_id' => session()->get('active_patient_id', $account->patients->first()?->id),
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'specialization' => $request->user()->specialization,
                ] : null,
                'patientAccount' => $patientAccountData,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
