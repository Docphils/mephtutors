<?php

namespace App\Livewire\Requests;

use App\Models\AcademicProgramme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.visitor')]
#[Title('Intervention Request - MephEd')]
class ProgrammeConsultationForm extends Component
{
    #[Url(as: 'programme')]
    public string $selected_programme = '';

    public ?AcademicProgramme $selectedProgramme = null;

    public function mount(): void
    {
        $this->selected_programme = trim($this->selected_programme);
        $this->resolveSelectedProgramme();
    }

    public function updatedSelectedProgramme($value): void
    {
        $this->selected_programme = trim((string) $value);
        $this->resolveSelectedProgramme();
    }

    public function selectProgramme(string $slug): void
    {
        $this->selected_programme = trim($slug);
        $this->resolveSelectedProgramme();
    }

    public function requestProgramme(string $slug): void
    {
        $this->selectProgramme($slug);
        $this->proceedWithSelectedProgramme();
    }

    public function proceedWithSelectedProgramme(): void
    {
        if (!$this->selectedProgramme) {
            return;
        }

        if (!Auth::check()) {
            $this->dispatch('intervention-selected');
            return;
        }

        $role = (string) Auth::user()->role;

        if ($role === 'client' && Route::has('client.interventions.create')) {
            $this->redirectRoute('client.interventions.create', ['programme' => $this->selectedProgramme->slug], navigate: true);
            return;
        }

        if ($role === 'admin' && Route::has('admin.dashboard')) {
            $this->redirectRoute('admin.dashboard', navigate: true);
            return;
        }

        if ($role === 'tutor' && Route::has('tutor.dashboard')) {
            $this->redirectRoute('tutor.dashboard', navigate: true);
        }
    }

    protected function resolveSelectedProgramme(): void
    {
        if ($this->selected_programme === '') {
            $this->selectedProgramme = null;
            return;
        }

        $this->selectedProgramme = AcademicProgramme::query()
            ->where('is_active', true)
            ->where('slug', $this->selected_programme)
            ->first();

        if (!$this->selectedProgramme) {
            $this->selected_programme = '';
        }
    }

    public function render()
    {
        $programmes = AcademicProgramme::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'summary', 'tagline']);

        if ($this->selectedProgramme && !$programmes->contains('id', $this->selectedProgramme->id)) {
            $this->selectedProgramme = null;
            $this->selected_programme = '';
        } elseif ($programmes->count() === 1) {
            $this->selected_programme = (string) optional($programmes->first())->slug;
            $this->resolveSelectedProgramme();
        }

        return view('livewire.requests.programme-consultation-form', [
            'programmes' => $programmes,
            'authRole' => Auth::user()->role ?? null,
        ]);
    }
}
