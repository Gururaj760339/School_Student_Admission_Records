<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates (or updates) the initial admin account.
 *
 * Credentials are read from the environment (ADMIN_NAME, ADMIN_EMAIL,
 * ADMIN_PASSWORD) so no password is ever hard-coded or committed. Run with:
 *
 *     php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('ADMIN_EMAIL'));
        $password = (string) env('ADMIN_PASSWORD');
        $name = trim((string) env('ADMIN_NAME', 'Administrator')) ?: 'Administrator';

        if ($email === '' || $password === '') {
            $this->command?->warn(
                'Skipping admin seeding: set ADMIN_EMAIL and ADMIN_PASSWORD in .env first, then rerun the seeder.'
            );

            return;
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password]
        );

        $this->command?->info('Admin account ready: '.$email);
    }
}
