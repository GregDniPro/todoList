<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users
        User::factory(10)->create([
            'password' => 'seed_pass'
        ]);

        // Seed parent tasks
        Task::factory(50)->create([
            'parent_id' => null
        ]);

        //Seed children tasks
        Task::factory(250)->create();
    }
}
