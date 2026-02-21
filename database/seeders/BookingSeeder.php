<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Booking;
use App\Models\TutorRequest;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        $recordCount = 50; // Adjust the number of records here

        $tutorRequests = TutorRequest::all();
        // Get all users with the 'client' role
        $tutor = User::where('role', 'tutor')->get();

        foreach ($tutorRequests as $tutorRequest) {
            $randomTutor = $tutor->isNotEmpty() ? $tutor->random() : null;
            Booking::create([
                'user_id' => $tutorRequest->user_id, // Assuming user is the client
                'client_id' => $tutorRequest->user_id,
                'tutor_id' => $randomTutor?->id,
                'tutor_request_id' => $tutorRequest->id,
                'start_date' => $tutorRequest->started_at ?? null,
                'end_date' => $tutorRequest->completed_at ?? null,
                'location' => $tutorRequest->lesson_address ?? $tutorRequest->address ?? null,
                'days_times' => trim((string)($tutorRequest->preferred_days ?? '')) . ' ' . trim((string)($tutorRequest->preferred_time ?? '')),
                'subjects' => $tutorRequest->additional_notes ?? null,
                'learners' => $tutorRequest->learners ?? null,
                'sessions' => $tutorRequest->sessions_per_week ?? null,
                'duration' => $tutorRequest->duration_per_session ?? null,
                'tutorGender' => ucfirst($tutorRequest->preferred_tutor_gender ?? 'any'),
                'curriculum' => $tutorRequest->curriculum ?? null,
                'status' => 'Pending',
                'classes' => 'Adult',
                'amount' => $tutorRequest->budget_max ?? $tutorRequest->budget_min ?? 0,
                'paymentStatus' => 'Pending',
                'tutorRemarks' => null,
                'clientAcceptanceRemarks' => null,
                'clientApprovalRemarks' => null,
            ]);
        }
    }
}