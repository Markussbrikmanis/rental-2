<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'role' => UserRole::Admin,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'role' => UserRole::Owner,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'tenant@example.com'],
            [
                'name' => 'Tenant User',
                'role' => UserRole::Tenant,
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        );
    }
}
