<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Crm;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('MephEd - Client Manager')]
class ClientManager extends Component
{

    public $activeTab = 'All'; 
    public $showModal = false;
    public $ClientId;
    public $search = '';
    public $showDetailModal = false;
    public $selectedClient;


    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function showClientDetails($id)
    {
        $this->selectedClient = User::where('role', 'client')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function getDetails()
    {
        $query = User::where('role', 'client');
    
        if ($this->search) {
            // Group the search conditions to ensure they apply only to fullName and email
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
    
        return $query->paginate(10);
    }

    public function render()
    {
        Gate::authorize('Admin');
        $clients = User::where('role', 'client')->paginate(10);
        $bookings = Booking::all();
        $crms = Crm::all();

        return view('livewire.admin.client-manager', [
            'getDetails' => $this->getDetails(),
            'clients' => $clients,
            'bookings' => $bookings,
            'crms' => $crms,
        ]);
    }
}
