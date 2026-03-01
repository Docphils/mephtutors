<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'MephEd' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
        <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
        <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17506686809">
        </script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-17506686809');
        </script>
    </head>
    <body>
        <div class="min-h-full bg-cyan-800 text-white ">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-cyan-600  shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
        @
        @livewireScripts
    </body>
</html>


<?php

namespace App\Livewire\Tutor;

use App\Mail\LessonCompleteEmail;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manage Lessons - MephEd Tutor')]
class TutorLessons extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $activeTab = '';
    public $showModal = false; 
    public $selectedLesson;
    public $tutorRemarks, $status;

    public $showCompletedModal = false;
   
    // Set the active tab
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Show the selected lesson in the modal
    public function showLesson($id)
    {
        $this->selectedLesson = Booking::find($id); 
        $this->showModal = true;
    } 

    // Open Completed Modal
    public function editCompleted($id)
    {
        $this->selectedLesson = Booking::findOrFail($id);
        Gate::authorize('Tutor');

        // Ensure the booking status is 'Completed' before showing the approval modal
        if ($this->selectedLesson->status !== 'Active' && $this->selectedLesson->status !== 'Declined') {
            session()->flash('error', 'You can only edit completion remarks for active or declined lessons.');
            return;
        }

        $this->resetFields();
        $this->showCompletedModal = true;
    }

   

    // Tutor Completed Remarks
    public function submitCompleted()
    {
        Gate::authorize('Tutor');

        // Ensure the status of the booking is still 'Active' before updating
        if ($this->selectedLesson->status !== 'Active' && $this->selectedLesson->status !== 'Declined') {
            session()->flash('error', 'You cannot submit approval remarks for lessons that are not active or declined.');
            return;
        }

        $this->validate([
            'tutorRemarks' => 'required|string',
            'status' => 'required|in:Completed',
        ]);

        $this->selectedLesson->update([
            'tutorRemarks' => $this->tutorRemarks,
            'status' => $this->status
        ]);
        if ($this->selectedLesson->status === 'Completed') {
            $this->selectedLesson->completed_at = now();
            $this->selectedLesson->save(); 
        }

        $completedLesson = $this->selectedLesson->refresh()->load('client');

        try {
            Mail::to($completedLesson->client->email)->send(new LessonCompleteEmail($completedLesson));
            session()->flash('success', 'Lesson updated successfully');
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());

            session()->flash('success', 'Lesson updated successfully (but email was not sent to client). Please contact our support team');
        }

        $this->resetFields();
        $this->showCompletedModal = false;

    }

    // Reset fields
    public function resetFields()
    {
        $this->tutorRemarks = '';
        $this->status = '';
    }

    
    public function getLessons()
    {
        $user = Auth::user();
        Gate::authorize('Tutor');

        // Retrieve bookings for the authenticated user based on the active tab
        switch ($this->activeTab) {
            case 'Closed Lessons':
                return Booking::where('tutor_id', $user->id)->where('status', 'Closed')->paginate(10);
            case 'Completed Lessons':
                return Booking::where('tutor_id', $user->id)->where('status', 'Completed')->paginate(10);
            case 'Active Lessons':
                return Booking::where('tutor_id', $user->id)->where('status', 'Active')->paginate(10);
            case 'Declined Lessons':
                return Booking::where('tutor_id', $user->id)->where('status', 'Declined')->paginate(10);
            case 'Accepted Lessons':
            default:
                return Booking::where('tutor_id', $user->id)->paginate(10); 
        }
    }

    public function render()
    {
        return view('livewire.tutor.tutor-lessons', [
            'lessons' => $this->getLessons(),
        ]);
    }}
