<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@gmail.com',
        //     'password' => bcrypt('welcome'),
        //     'role_id' => '1'
        // ]);

        // User creation has been centralized in CreateUserService.
        app(\App\Services\CreateUserService::class)->create([
            'name' => 'Admin',
            'email' => 'superadmin@gmail.com',
            'password' => 'Welcome@1',
            'role_id' => 1,
            'is_active' => true,
            'is_deleted' => false,
            'created_by' => null, // system seeded
        ]);

    }
}
