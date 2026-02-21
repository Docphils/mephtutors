<?php

namespace App\Livewire;


use Livewire\Component;
use App\Models\UserProfile as Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserProfile extends Component
{
    public $userProfile;

    public function mount()
    {
        Gate::authorize('AdminOrClient');

        $this->userProfile = Auth::user()->userProfile;
    }

    public function openProfileModal()
    {
        $this->dispatch('edit-user-profile');
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}
