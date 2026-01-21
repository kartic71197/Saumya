<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateUserService
{
    /**
     * Centralised user creation logic.
     *
     * NOTE:
     * - Validation is handled outside (Controller / Livewire)
     * - Permissions are handled outside
     * - This service ONLY creates the user + shared side effects
     */
    public function create(array $data, array $options = []): User
    {
        // Prevent duplicate ACTIVE users (common logic across app)
        if (!empty($data['email'])) {
            if (
                User::where('email', $data['email'])
                    ->where('is_active', true)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'email' => 'The email is already taken.',
                ]);
            }
        }
        
        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password'] ?? Str::random(12)),
            'avatar'          => $data['avatar'] ?? 'avatar (8).png',
            'role_id'         => $data['role_id'],
            'organization_id' => $data['organization_id'] ?? null,
            'location_id'     => $data['location_id'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'created_by'      => $data['created_by'] ?? (Auth::check() ? Auth::id() : null),
            'is_active'       => $data['is_active'] ?? true,
            'is_deleted'      => $data['is_deleted'] ?? false,
            'system_locked'   => $data['system_locked'] ?? false,
            'is_medical_rep'  => $data['is_medical_rep'] ?? false,
        ]);

        // Optional: login user (signup flow)
        if (!empty($options['login'])) {
            Auth::login($user);
        }

        // Optional: send notification
        if (!empty($options['notify'])) {
            $user->notify($options['notify']);
        }

        // Optional: audit logging
        if (!empty($options['audit'])) {
            app(\App\Services\UserLocationAuditService::class)
                ->logUserCreate($user);
        }

        return $user;
    }
}
