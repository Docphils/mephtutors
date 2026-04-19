<?php

namespace Database\Seeders;

use App\Models\AcademicProgramme;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicProgrammesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjectBank = [
            'English Language',
            'Mathematics',
            'Further Mathematics',
            'Biology',
            'Chemistry',
            'Physics',
            'Agricultural Science',
            'Economics',
            'Commerce',
            'Accounting',
            'Government',
            'Literature in English',
            'Geography',
            'History',
            'Civic Education',
            'Christian Religious Studies',
            'Islamic Religious Studies',
            'Computer Studies',
            'Technical Drawing',
            'Food and Nutrition',
        ];

        $service = Service::query()->updateOrCreate(
            ['slug' => 'academic-programmes'],
            [
                'name' => 'Academic Programmes',
                'description' => 'Structured exam and term-support programmes focused on learner performance outcomes.',
                'target' => 'tutor_request',
                'is_active' => true,
            ]
        );

        $programmes = [
            [
                'name' => 'WAEC Final Sprint',
                'slug' => 'waec-final-sprint',
                'tagline' => 'Focused WAEC preparation for students who need structure, clarity, and stronger performance before the final papers.',
                'summary' => 'Monthly WAEC preparation programme, renewable until examination season is complete.',
                'overview' => 'WAEC Final Sprint combines exam-driven planning, active revision, and consistent tutor support so learners build clarity and perform with confidence.',
                'who_it_is_for' => 'Students preparing for WAEC who need guided preparation, revision structure, and accountability.',
                'what_parents_can_expect' => 'Clear study structure, focused subject support, and regular progress feedback before final papers.',
                'starting_from_text' => 'Starting from NGN 45,000 monthly',
                'pricing_note' => 'Price depends on frequency, session duration, lesson mode, and location for home lessons.',
                'renewability_note' => 'Renewable monthly till examination period ends.',
                'frequency_options' => ['2x weekly', '3x weekly', '6x weekly'],
                'duration_options' => ['1 hour', '2 hours'],
                'mode_options' => ['online', 'home lessons'],
                'subject_options' => $subjectBank,
                'max_selectable_subjects' => 8,
                'pricing_matrix' => [
                    'frequency_prices' => ['2x weekly' => 45000, '3x weekly' => 65000, '6x weekly' => 115000],
                    'duration_multipliers' => ['1 hour' => 1.0, '2 hours' => 1.75],
                    'mode_multipliers' => ['online' => 1.0, 'home' => 1.2],
                    'additional_subject_fraction' => 0.35,
                    'base_subject_allowance' => 1,
                    'location_surcharge' => 5000,
                ],
                'faq_items' => [
                    ['q' => 'Can we request more than one subject?', 'a' => 'Yes. Parents can request one or multiple subjects based on learner needs.'],
                    ['q' => 'How long does the programme run?', 'a' => 'The package is monthly and can be renewed until exams are done.'],
                ],
                'sort_order' => 1,
                'is_active' => true,
                'hero_image' => 'images/b-waec.jpeg',
            ],
            [
                'name' => 'NECO Final Sprint',
                'slug' => 'neco-final-sprint',
                'tagline' => 'Targeted NECO preparation designed to strengthen understanding, improve speed, and support better exam performance.',
                'summary' => 'Monthly NECO preparation programme with flexible renewal.',
                'overview' => 'NECO Final Sprint provides structured support around key topics, past-question practice, and confidence-building before final papers.',
                'who_it_is_for' => 'Learners preparing for NECO who need stronger understanding, improved speed, and focused exam strategy.',
                'what_parents_can_expect' => 'Regular tutoring rhythm, measurable support around weak areas, and practical exam readiness.',
                'starting_from_text' => 'Starting from NGN 45,000 monthly',
                'pricing_note' => 'Price depends on frequency, session duration, lesson mode, and location for home lessons.',
                'renewability_note' => 'Renewable monthly till examination period ends.',
                'frequency_options' => ['2x weekly', '3x weekly', '6x weekly'],
                'duration_options' => ['1 hour', '2 hours'],
                'mode_options' => ['online', 'home lessons'],
                'subject_options' => $subjectBank,
                'max_selectable_subjects' => 8,
                'pricing_matrix' => [
                    'frequency_prices' => ['2x weekly' => 43000, '3x weekly' => 62000, '6x weekly' => 108000],
                    'duration_multipliers' => ['1 hour' => 1.0, '2 hours' => 1.75],
                    'mode_multipliers' => ['online' => 1.0, 'home' => 1.18],
                    'additional_subject_fraction' => 0.34,
                    'base_subject_allowance' => 1,
                    'location_surcharge' => 4500,
                ],
                'faq_items' => [
                    ['q' => 'Is this only for final-year candidates?', 'a' => 'It is best suited for candidates approaching NECO examinations.'],
                    ['q' => 'Can we combine science and arts subjects?', 'a' => 'Yes, subject combinations are supported based on schedule and tutor availability.'],
                ],
                'sort_order' => 2,
                'is_active' => true,
                'hero_image' => 'images/banner2.jpg',
            ],
            [
                'name' => 'NABTEB Final Sprint',
                'slug' => 'nabteb-final-sprint',
                'tagline' => 'Practical and structured NABTEB preparation for candidates who need guided support ahead of their examinations.',
                'summary' => 'Monthly NABTEB preparation with guided support and renewal options.',
                'overview' => 'NABTEB Final Sprint supports candidates with practical and theory preparation through a predictable tutoring schedule.',
                'who_it_is_for' => 'Candidates writing NABTEB who need consistent support and structured exam preparation.',
                'what_parents_can_expect' => 'Topic-by-topic guidance, focused revision, and better readiness before final papers.',
                'starting_from_text' => 'Starting from NGN 45,000 monthly',
                'pricing_note' => 'Price depends on frequency, session duration, lesson mode, and location for home lessons.',
                'renewability_note' => 'Renewable monthly till examination period ends.',
                'frequency_options' => ['2x weekly', '3x weekly', '6x weekly'],
                'duration_options' => ['1 hour', '2 hours'],
                'mode_options' => ['online', 'home lessons'],
                'subject_options' => $subjectBank,
                'max_selectable_subjects' => 7,
                'pricing_matrix' => [
                    'frequency_prices' => ['2x weekly' => 42000, '3x weekly' => 60000, '6x weekly' => 105000],
                    'duration_multipliers' => ['1 hour' => 1.0, '2 hours' => 1.7],
                    'mode_multipliers' => ['online' => 1.0, 'home' => 1.2],
                    'additional_subject_fraction' => 0.33,
                    'base_subject_allowance' => 1,
                    'location_surcharge' => 5000,
                ],
                'faq_items' => [
                    ['q' => 'Do you support practical-focused preparation?', 'a' => 'Yes, tutors align preparation to practical and theoretical exam requirements.'],
                    ['q' => 'Can lessons hold at home?', 'a' => 'Yes. Parents may choose online or home lessons.'],
                ],
                'sort_order' => 3,
                'is_active' => true,
                'hero_image' => 'images/home_tutoring.jpg',
            ],
            [
                'name' => 'Third Term Performance Boost',
                'slug' => 'third-term-performance-boost',
                'tagline' => 'Support your child through third term with focused lessons that improve understanding, class performance, and confidence before final examinations.',
                'summary' => 'Monthly third-term support programme, renewable through the term.',
                'overview' => 'Third Term Performance Boost helps learners improve class understanding, CA outcomes, and readiness before promotion or final examinations.',
                'who_it_is_for' => 'Learners who need stronger third-term performance and structured academic support.',
                'what_parents_can_expect' => 'Better classroom understanding, improved CA consistency, and exam-readiness support.',
                'starting_from_text' => 'Starting from NGN 38,000 monthly',
                'pricing_note' => 'Price depends on frequency, session duration, lesson mode, and location for home lessons.',
                'renewability_note' => 'Renewable monthly throughout third term.',
                'frequency_options' => ['2x weekly', '3x weekly', '6x weekly'],
                'duration_options' => ['1 hour', '2 hours'],
                'mode_options' => ['online', 'home lessons'],
                'subject_options' => $subjectBank,
                'max_selectable_subjects' => 10,
                'pricing_matrix' => [
                    'frequency_prices' => ['2x weekly' => 38000, '3x weekly' => 56000, '6x weekly' => 98000],
                    'duration_multipliers' => ['1 hour' => 1.0, '2 hours' => 1.7],
                    'mode_multipliers' => ['online' => 1.0, 'home' => 1.15],
                    'additional_subject_fraction' => 0.30,
                    'base_subject_allowance' => 2,
                    'location_surcharge' => 4000,
                ],
                'faq_items' => [
                    ['q' => 'Is this exam-specific?', 'a' => 'It is term-performance-focused, with optional exam readiness support.'],
                    ['q' => 'Can we start mid-term?', 'a' => 'Yes. The programme can start any time and renew through term end.'],
                ],
                'sort_order' => 4,
                'is_active' => true,
                'hero_image' => 'images/banner.jpg',
            ],
            [
                'name' => '30-Day Subject Rescue',
                'slug' => '30-day-subject-rescue',
                'tagline' => 'A focused one-month programme for students who need urgent help in a specific subject.',
                'summary' => 'A fixed 30-day intervention focused on one or multiple subjects.',
                'overview' => '30-Day Subject Rescue is designed for urgent academic intervention with focused sessions on immediate learning gaps.',
                'who_it_is_for' => 'Learners needing rapid support in a specific subject before tests, exams, or major class milestones.',
                'what_parents_can_expect' => 'Fast intervention pacing, targeted weak-area support, and practical improvement tracking over 30 days.',
                'starting_from_text' => 'Starting from NGN 30,000 for 30 days',
                'pricing_note' => 'Price depends on frequency, session duration, lesson mode, location for home lessons, and subject count.',
                'renewability_note' => 'Runs for exactly 30 days per subscription cycle.',
                'frequency_options' => ['2x weekly', '3x weekly'],
                'duration_options' => ['1 hour', '2 hours'],
                'mode_options' => ['online', 'home lessons'],
                'subject_options' => $subjectBank,
                'max_selectable_subjects' => 4,
                'pricing_matrix' => [
                    'frequency_prices' => ['2x weekly' => 30000, '3x weekly' => 42000],
                    'duration_multipliers' => ['1 hour' => 1.0, '2 hours' => 1.65],
                    'mode_multipliers' => ['online' => 1.0, 'home' => 1.15],
                    'additional_subject_fraction' => 0.40,
                    'base_subject_allowance' => 1,
                    'location_surcharge' => 3500,
                ],
                'faq_items' => [
                    ['q' => 'Can we add more than one subject?', 'a' => 'Yes. Multiple subjects are supported, with pricing adjusted for subject count.'],
                    ['q' => 'Can this run beyond 30 days?', 'a' => 'The package is fixed for 30 days; continuation can be handled as a new cycle.'],
                ],
                'sort_order' => 5,
                'is_active' => true,
                'hero_image' => 'images/banner2.jpg',
            ],
        ];

        foreach ($programmes as $programme) {
            $serviceItem = ServiceItem::query()->updateOrCreate(
                ['slug' => $programme['slug'] . '-programme'],
                [
                    'service_id' => $service->id,
                    'name' => $programme['name'],
                    'description' => $programme['summary'],
                    'image_path' => $programme['hero_image'],
                    'target' => 'tutor_request',
                    'shown_on_welcome' => false,
                    'display_position' => $programme['sort_order'],
                    'has_subjects' => true,
                    'requires_curriculum' => false,
                    'requires_level' => true,
                    'requires_exam_type' => false,
                    'is_active' => true,
                ]
            );

            $programme['service_item_id'] = $serviceItem->id;

            AcademicProgramme::query()->updateOrCreate(
                ['slug' => $programme['slug']],
                $programme
            );
        }

        SiteSetting::setValue('homepage_mode', 'academic');
        SiteSetting::setValue('programme_trial_class_enabled', true);
        SiteSetting::setValue('programme_probationary_classes_allowed', true);
        SiteSetting::setValue(
            'programme_pricing_note_default',
            'Pricing depends on frequency, session duration, mode, and location for physical lessons.'
        );
        SiteSetting::setValue('programme_default_subject_limit', 6);
        SiteSetting::setValue('programme_flow_payment_required', true);
        SiteSetting::setValue('programme_service_slug', 'academic-programmes');
    }
}
