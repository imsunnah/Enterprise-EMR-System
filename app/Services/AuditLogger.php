<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log a PHI access or state modification.
     */
    public static function log(
        string $action,
        string $entityName,
        string $entityId,
        ?array $changes = null,
        ?Request $request = null
    ): AuditLog {
        $req = $request ?: request();

        $userId = null;
        $userRole = null;
        $userName = null;

        if (Auth::check()) {
            $user = Auth::user();
            $userId = $user->id;
            $userRole = $user->role;
            $userName = $user->name;
        } elseif (session()->has('patient_account_id')) {
            $patientAccountId = session()->get('patient_account_id');
            $userId = $patientAccountId;
            $userRole = 'PATIENT';
            $userName = 'Patient Portal (' . session()->get('patient_phone', 'User') . ')';
        }

        return AuditLog::create([
            'user_id' => $userId,
            'user_role' => $userRole ?: 'SYSTEM',
            'user_name' => $userName ?: 'System Process',
            'action' => strtoupper($action),
            'entity_name' => $entityName,
            'entity_id' => $entityId,
            'ip_address' => $req ? $req->ip() : null,
            'user_agent' => $req ? substr($req->userAgent() ?? '', 0, 500) : null,
            'changes' => $changes,
        ]);
    }
}
