<?php

namespace App\Livewire\Client;

use App\Models\AcademicProgramme;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('New Intervention Request')]
class InterventionRequestCreate extends Component
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
        } elseif (Auth::check() && $programmes->count() === 1) {
            $this->selected_programme = (string) optional($programmes->first())->slug;
            $this->resolveSelectedProgramme();
        }

        return view('livewire.client.intervention-request-create', [
            'programmes' => $programmes,
        ]);
    }
}
