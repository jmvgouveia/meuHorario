<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\TimePeriod;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        TimePeriod::create(['description' => '08:00 - 09:00']);
        TimePeriod::create(['description' => '09:00 - 10:00']);
        TimePeriod::create(['description' => '10:00 - 11:00']);
        TimePeriod::create(['description' => '11:00 - 12:00']);
        // ... add more as needed
    }
}
