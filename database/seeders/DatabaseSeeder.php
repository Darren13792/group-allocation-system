<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed data for settings

        DB::table('settings')->insert([
            [
                'min_group_size' => 4,
                'max_group_size' => 6,
                'max_groups_per_topic' => 2,
                'preference_size' => 4,
                'ideal_size' => 5,
            ],
        ]);

        // Seed data for users
        \App\Models\User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin',
            'password' => bcrypt('password'),
            'activation_status' => true,
            'user_type' => 'ADMIN',
        ]);

        \App\Models\User::factory()->create([
            'first_name' => 'Student',
            'last_name' => 'Student',
            'email' => 'student',
            'password' => bcrypt('password'),
            'activation_status' => true,
            'user_type' => 'STUDENT',
        ]);

        \App\Models\User::factory()->create([
            'first_name' => 'Supervisor',
            'last_name' => 'Supervisor',
            'email' => 'supervisor',
            'password' => bcrypt('password'),
            'activation_status' => true,
            'user_type' => 'SUPERVISOR',

        ]);
    }
}
