<?php

namespace App\Livewire\Pages;

use App\Models\Enrollee;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.visitor')]
class BootcampEnrollmentSuccess extends Component
{
    public Enrollee $enrollee;

    public function mount(Enrollee $enrollee): void
    {
        abort_unless(($enrollee->meta['payment_status'] ?? null) === 'paid', 404);
        $this->enrollee = $enrollee->load('cohort.service', 'cohort.serviceItem');
    }

    public function render()
    {
        $cohort = $this->enrollee->cohort;
        $title = 'Registration Confirmed | MephEd';
        $description = 'Your bootcamp registration and payment have been confirmed.';

        return view('livewire.pages.bootcamp-enrollment-success', [
            'enrollee' => $this->enrollee,
            'cohort' => $cohort,
        ])->title($title)->layoutData([
            'metaDescription' => $description,
            'canonicalUrl' => route('bootcamp.enrollment.success', ['enrollee' => $this->enrollee->id]),
            'ogType' => 'website',
            'ogImage' => asset('images/banner.jpg'),
        ]);
    }
}