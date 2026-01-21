<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\StaffRoleCreation;
use App\Mail\NewOrganizationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


/**
 * Handles creation of a Practice (Organization) in one place.
 * This keeps practice creation consistent across Admin and other flows.
 */
class CreatePracticeService
{
    public function handle(array $data): Organization
    {
        return DB::transaction(function () use ($data) {

            // 1️⃣ Create Practice (Organization)
            $organization = Organization::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'],
                'state' => $data['state'],
                'city' => $data['city'],
                'address' => $data['address'],
                'pin' => $data['pin'],
                'image' => $data['logo'] ?? null,
                'is_rep_org' => $data['is_rep_org'] ?? auth()->user()->is_medical_rep ?? false,
                'is_active' => true,
            ]);

            // 2️⃣ Generate organization code
            $organization->organization_code = 10000 + $organization->id;
            $organization->save();

            // 3️⃣ Create default Staff role and permissions
            app(StaffRoleCreation::class)->create($organization->id);

            // 4️⃣ Create system Admin user for the practice
            $orgName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $organization->name));

            User::create([
                'name' => 'Admin',
                'email' => 'admin@' . $orgName . '.com',
                'password' => Hash::make('Admin123!'),
                'role_id' => 2, // Practice Admin
                'organization_id' => $organization->id,
                'system_locked' => true,
                'is_medical_rep' => $data['is_rep_org'] ?? false,
                'avatar' => 'avatar (8).png',
            ]);

            // 5. Sending email to healthshade support
            $adminUser = auth()->user();
            try {
                
                Mail::to('support@healthshade.com')
                    ->send(new NewOrganizationMail($adminUser, $organization));
            } catch (\Exception $e) {
                Log::error('Failed to send practice creation email', [
                    'org_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
            }


            return $organization;
        });
    }
}
