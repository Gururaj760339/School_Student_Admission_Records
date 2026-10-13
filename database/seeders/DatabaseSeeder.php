<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Only the admin account is seeded. Student admission records live
     * exclusively in Google Sheets and are never stored in the database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
    }
}
