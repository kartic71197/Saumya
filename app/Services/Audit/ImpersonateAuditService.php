<?php

namespace App\Services\Audit;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImpersonateAuditService
{
    /**
     * Log the start of impersonation
     */
    public function impersonateStart($superadminId, $adminId, $organizationId)
    {
        try {
            $currentUser = User::find($adminId);
            if ($currentUser->system_locked == true) {
                return;
            }
            DB::table('audits')->insert([
                'user_id' => $superadminId,
                'event' => 'Start Impersonation',
                'auditable_type' => 'User',
                'auditable_id' => $adminId,
                'old_values' => json_encode([
                    'user_id' => $superadminId,
                ]),
                'new_values' => json_encode([
                    'user_id' => $adminId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'organization_id' => $organizationId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log start impersonation audit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log the end of impersonation
     */
    public function impersonateEnd($superadminId, $adminId, $organizationId)
    {
        try {

            $currentUser = User::find($adminId);
            if ($currentUser->system_locked == true) {
                return;
            }

            DB::table('audits')->insert([
                'user_id' => $superadminId,
                'event' => 'End Impersonation',
                'auditable_type' => 'User',
                'auditable_id' => $adminId,
                'old_values' => json_encode([
                    'user_id' => $adminId,
                ]),
                'new_values' => json_encode([
                    'user_id' => $superadminId,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'organization_id' => $organizationId,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log end impersonation audit: ' . $e->getMessage());
            return false;
        }
    }
}
