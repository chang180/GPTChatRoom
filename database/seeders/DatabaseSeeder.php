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

        if (!User::where('name', 'test1234')->exists()) {
            User::factory()->create([
                'name' => 'test1234',
                'email' => 'test1234@example.com',
            ]);
        }
    }
}
