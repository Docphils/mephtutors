<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(3)->create();
        $this->call(ServiceAndItemsSeeder::class);
        $this->call(ExamTypesAndLevelsSeeder::class);
        $this->call(AcademicProgrammesSeeder::class);


        //  \App\Models\User::factory()->create([
        //    'name' => 'Another User',
        //    'email' => 'new@example.com',
        //    'role' => 'tutor',
        // ]);
    }
}
