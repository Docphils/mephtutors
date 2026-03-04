<?php

namespace App\Livewire\Tutor;

use App\Models\TutorProfile;
use Illuminate\Support\Facades\{Auth, Gate, Storage, Log, Mail};
use Livewire\{Component, WithFileUploads, Attributes\Layout, Attributes\Title};

#[Layout('layouts.app')]
#[Title('Profile Management - MephEd')]
class TutorProfiles extends Component
{
    use WithFileUploads;

    // View State
    public $activeSection = 'view_all'; // personal, academic, banking, media

    // Model Data
    public $profile;
    
    // Form Fields (Unified for the component state)
    public $fullName, $phone, $state, $city, $address, $DOB, $gender;
    public $qualification, $discipline, $experience, $careerProfile;
    public $bankName, $accountName, $accountNumber;
    public $image, $CV, $video;
    public $editor = false;
    public $states = [
        'Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','FCT'
    ];

    public function mount()
    {
        Gate::authorize('Tutor');
        $this->loadProfileData();
    }

    public function loadProfileData()
    {
        $this->profile = Auth::user()->tutorProfile;

        if ($this->profile) {
            $this->fill($this->profile->toArray());
        }

          // Reset upload fields
        $this->image = null;
        $this->CV = null;
        $this->video = null;
    }

    public function setEditor()
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $this->editor = true;
        $this->activeSection = 'personal';
        
    }

    public function setSection($section)
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $this->activeSection = $section;
        if($this->activeSection === 'view_all'){
            $this->editor = false;
        }
    }

    // Individual Save Methods for better UX
    public function savePersonal()
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $data = $this->validate([
            'fullName' => 'required|string|max:255',
            'phone' => 'required|numeric',
            'state' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string|max:255',
            'DOB' => 'required|date',
            'gender' => 'required|in:Male,Female',
        ]);

        $this->updateOrCreateProfile($data);
        session()->flash('success', 'Personal information updated.');
    }

    public function saveAcademic()
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $data = $this->validate([
            'qualification' => 'required|in:SSCE,Diploma,NCE,HND/Bachelors,MSc/MA,PhD',
            'discipline'    => 'required|string|max:255',
            'experience'    => 'required|in:0-1 year,2-5 years,6-10 years,Above 10 years',
            'careerProfile' => 'required|string|min:50',
            'CV'            => ($this->profile && $this->profile->CV) ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',

        ]);

        if ($this->CV && !is_string($this->CV)) {
            // Delete old CV if it exists
            if ($this->profile && $this->profile->CV) {
                Storage::disk('public')->delete($this->profile->CV);
            }

            // Save new CV
            $data['CV'] = $this->CV->store('cvs', 'public');
        } else {
            unset($data['CV']); // keep existing CV untouched
        }


        $this->updateOrCreateProfile($data);
        session()->flash('success', 'Academic records updated.');
    }

    public function saveBanking()
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $data = $this->validate([
            'bankName'     => 'required|string|max:255',
            'accountName'  => 'required|string|max:255',
            'accountNumber'=> 'required|digits:10',
        ]);


        $this->updateOrCreateProfile($data);
        session()->flash('success', 'Payment details secured.');
    }

    public function saveMedia()
    {
        if($this->profile && $this->profile->status === 'Approved')
        {
            session()->flash('error', 'You can not edit an approved profile. Request edit access.');
            return;
        }
        $this->validate([
            'image' => ($this->profile && $this->profile->image) 
                ? 'nullable|image|max:2048' 
                : 'required|image|max:2048',
            'video' => ($this->profile && $this->profile->video) 
                ? 'nullable|mimes:mp4,mov,avi|max:20480' 
                : 'required|mimes:mp4,mov,avi|max:20480',

        ]);

        if ($this->image && !is_string($this->image)) {
            if ($this->profile && $this->profile->image) {
                Storage::disk('public')->delete($this->profile->image);
            }
            $data['image'] = $this->image->store('tutor_images', 'public');
        }

        if ($this->video && !is_string($this->video)) {
            if ($this->profile && $this->profile->video) {
                Storage::disk('public')->delete($this->profile->video);
            }
            $data['video'] = $this->video->store('tutor_videos', 'public');
        }


        if(!empty($data)) {
            $this->updateOrCreateProfile($data);
            session()->flash('success', 'Media files updated.');
        }else{
            session()->flash('error', 'No new media selected to upload.');
            return;
        }
    }

    protected function updateOrCreateProfile($data)
    {
        $profile = TutorProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            array_merge($data, ['approvalRemarks' => $this->profile->approvalRemarks ?? 'N/A'])
        );
        $this->loadProfileData();
    }

    public function render()
    {
        return view('livewire.tutor.tutor-profiles');
    }
}