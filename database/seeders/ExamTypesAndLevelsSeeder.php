<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Level;
use App\Models\ExamType;

class ExamTypesAndLevelsSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            ['name' => 'Primary', 'order' => 1],
            ['name' => 'Junior Secondary', 'order' => 2],
            ['name' => 'Senior Secondary', 'order' => 3],
            ['name' => 'Undergraduate', 'order' => 4],
            ['name' => 'Professional', 'order' => 5],
        ];

        foreach ($levels as $data) {
            Level::updateOrCreate(['name' => $data['name']], $data);
        }

        $examTypes = [
            ['name' => 'WAEC', 'slug' => 'waec'],
            ['name' => 'NECO', 'slug' => 'neco'],
            ['name' => 'JAMB', 'slug' => 'jamb'],
            ['name' => 'IELTS', 'slug' => 'ielts'],
            ['name' => 'TOEFL', 'slug' => 'toefl'],
            ['name' => 'SAT', 'slug' => 'sat'],
        ];

        foreach ($examTypes as $data) {
            ExamType::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
