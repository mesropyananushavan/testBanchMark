<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AuthUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! (bool) config('authorization.seed_test_users_in_production', false)) {
            return;
        }

        $defaultPassword = (string) config('authorization.default_seed_password', 'password');

        foreach ((array) config('authorization.seed_users', []) as $seedUser) {
            $user = User::query()->firstOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'password' => Hash::make($defaultPassword),
                ],
            );

            if ($user->name !== $seedUser['name']) {
                $user->forceFill(['name' => $seedUser['name']])->save();
            }

            $user->syncRoles([$seedUser['role']]);
        }
    }
}

