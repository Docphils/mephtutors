<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\ServiceItem;

class ServiceAndItemsSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Top Level Services (Idempotent by slug)
        |--------------------------------------------------------------------------
        */

        $tutoring = Service::updateOrCreate(
            ['slug' => 'tutoring'],
            [
                'name' => 'Tutoring',
                'description' => 'Home and online tutoring services',
                'target' => 'tutor_request',
            ]
        );

        $coding = Service::updateOrCreate(
            ['slug' => 'coding-it'],
            [
                'name' => 'Coding & IT',
                'description' => 'Coding classes, private lessons and design',
                'target' => 'tutor_request',
            ]
        );

        $clubs = Service::updateOrCreate(
            ['slug' => 'school-clubs'],
            [
                'name' => 'School Clubs',
                'description' => 'Clubs and extracurricular programs for schools',
                'target' => 'institutions',
            ]
        );

        $consult = Service::updateOrCreate(
            ['slug' => 'consultation'],
            [
                'name' => 'Consultation',
                'description' => 'EdTech and school consultancy services',
                'target' => 'institutions',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Service Items (Aligned With Migration + Welcome Page Images)
        |--------------------------------------------------------------------------
        */

        $items = [

            // ---------------------------
            // Tutoring
            // ---------------------------
            [
                'slug' => 'home-tutoring',
                'service' => $tutoring,
                'name' => 'Home Tutoring',
                'description' => 'Personalized home lessons across subjects and levels.',
                'image_path' => '/images/b-home-tutoring.jpg',
                'display_position' => 1,
                'target' => 'tutor_request',
                'has_subjects' => true,
                'requires_curriculum' => true,
                'requires_level' => true,
                'requires_exam_type' => false,
            ],

            [
                'slug' => 'exam-prep',
                'service' => $tutoring,
                'name' => 'Exam Preparation',
                'description' => 'Structured preparation for WAEC, NECO, JAMB, BECE, IELTS and more.',
                'image_path' => '/images/b-waec.jpeg',
                'display_position' => 2,
                'target' => 'tutor_request',
                'requires_curriculum' => false,
                'has_subjects' => true,
                'requires_level' => true,
                'requires_exam_type' => true,
            ],

            [
                'slug' => 'music-lessons',
                'service' => $tutoring,
                'name' => 'Music Lessons',
                'description' => 'Professional music training for piano, voice, guitar and more.',
                'image_path' => '/images/b-musicClasses.jpeg',
                'display_position' => 3,
                'target' => 'tutor_request',
                'has_subjects' => true,
                'requires_curriculum' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],

            // ---------------------------
            // Coding & IT
            // ---------------------------
            [
                'slug' => 'coding-bootcamp',
                'service' => $coding,
                'name' => 'Coding Bootcamp',
                'description' => 'Learn modern programming from beginner to advanced level.',
                'image_path' => '/images/coding-banner2.jpeg',
                'display_position' => 4,
                'target' => 'tutor_request',
                'has_subjects' => false,
                'requires_curriculum' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],

            [
                'slug' => 'private-coding',
                'service' => $coding,
                'name' => 'Private Coding Lessons',
                'description' => 'One-on-one or small group coding sessions.',
                'image_path' => '/images/coding-banner2.jpeg',
                'display_position' => 5,
                'target' => 'tutor_request',
                'has_subjects' => false,
                'requires_curriculum' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],

            [
                'slug' => 'design',
                'service' => $coding,
                'name' => 'Graphic Design',
                'description' => 'Master graphic design tools and creative principles.',
                'image_path' => '/images/b-graphic-design.jpeg',
                'display_position' => 6,
                'target' => 'tutor_request',
                'has_subjects' => false,
                'requires_curriculum' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],

            // ---------------------------
            // School Clubs
            // ---------------------------
            [
                'slug' => 'school-clubs',
                'service' => $clubs,
                'name' => 'School Clubs',
                'description' => 'Structured extracurricular club programs for schools.',
                'image_path' => '/images/b-smartSchool.png',
                'display_position' => 7,
                'target' => 'institutions',
                'requires_curriculum' => false,
                'has_subjects' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],

            // ---------------------------
            // Consultation
            // ---------------------------
            [
                'slug' => 'consultation',
                'service' => $consult,
                'name' => 'Consultation',
                'description' => 'EdTech, IT and school transformation consultancy.',
                'image_path' => '/images/b-edtechStrategy.jpeg',
                'display_position' => 8,
                'target' => 'institutions',
                'has_subjects' => false,
                'requires_curriculum' => false,
                'requires_level' => false,
                'requires_exam_type' => false,
            ],
        ];

        foreach ($items as $item) {
            ServiceItem::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'service_id' => $item['service']->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'shown_on_welcome' => true,
                    'display_position' => $item['display_position'],
                    'image_path' => $item['image_path'],
                    'target' => $item['target'],
                    'has_subjects' => $item['has_subjects'],
                    'requires_curriculum' => $item['requires_curriculum'],
                    'requires_level' => $item['requires_level'],
                    'requires_exam_type' => $item['requires_exam_type'],
                    'is_active' => true,
                ]
            );
        }
    }
}