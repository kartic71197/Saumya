<?php

namespace App\Services;

use App\Models\Roles;
use Illuminate\Support\Facades\DB;

class StaffRoleCreation
{
    /**
     * Fixed permission IDs for Staff role
     */
    protected array $permissionIds = [
        8, 12, 15, 16, 18, 19, 21, 28,
        34, 38, 39, 41, 40, 22, 23,
        25, 30, 31,
    ];

    /**
     * Create Staff role and assign permissions
     */
    public function create(int $organizationId): Roles
    {
        return DB::transaction(function () use ($organizationId) {

            // Create or get Staff role
            $role = Roles::firstOrCreate(
                [
                    'role_name'       => 'Staff',
                    'organization_id' => $organizationId,
                ],
                [
                    'role_description' => 'Default staff role',
                    'is_active'        => true,
                ]
            );

            // Attach permissions (avoid duplicates)
            $role->permissions()->syncWithoutDetaching($this->permissionIds);

            return $role;
        });
    }
}
