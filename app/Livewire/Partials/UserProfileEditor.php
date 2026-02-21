<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use App\Models\UserProfile as Profile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\WithFileUploads;

class UserProfileEditor extends Component
{

    use WithFileUploads;

    public $userProfile;
    public $fullname, $address, $image, $gender, $DOB, $phone, $state, $city;
    public $states = [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','FCT'
    ];

    protected $rules = [
        'fullname' => 'required|string',
        'address' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'gender' => 'required|in:Male,Female',
        'DOB' => 'required|date',
        'state' => 'required|string',
        'city' => 'required|string',
        'phone' => 'required|string|max:16|regex:/^[0-9\s\-\+\(\)]*$/',
    ];

    public function mount()
    {
        Gate::authorize('AdminOrClient');

        $this->userProfile = Auth::user()->userProfile;

        if ($this->userProfile) {
            $this->loadUserProfileData();
        }
    }

    
    public function loadUserProfileData()
    {
        $this->fullname = Auth::user()->name;
        $this->address = $this->userProfile->address;
        $this->gender = $this->userProfile->gender;
        $this->DOB = optional($this->userProfile->DOB)->format('Y-m-d');
        $this->phone = $this->userProfile->phone;
        $this->state = $this->userProfile->state;
        $this->city = $this->userProfile->city;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'address' => $this->address,
            'gender' => $this->gender,
            'DOB' => $this->DOB,
            'phone' => $this->phone,
            'state' => $this->state,
            'city' => $this->city,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('images', 'public');
        }

        if ($this->userProfile) {
            // Update profile
            $this->userProfile->update($data);
            // Keep user's display name in sync
            $u = Auth::user();
            $u->name = $this->fullname;
            $u->save();
            session()->flash('success', 'Profile update successful.');
        } else {
            // Create profile
            $data['user_id'] = Auth::id();
            Profile::create($data);
            // Update user name as display fullname
            $u = Auth::user();
            $u->name = $this->fullname;
            $u->save();
            session()->flash('success', 'Profile created successfully.');
        }
        $this->dispatch('close-profile-editor');
    }

    public function closeProfileModal()
    {
        $this->dispatch('close-profile-editor');
    }


    public function render()
    {
        return view('livewire.partials.user-profile-editor');
    }
}
