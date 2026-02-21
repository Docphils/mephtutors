<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manage testimonials - MephEd')]
class AdminIndexTestimonials extends Component
{
    use WithPagination;

    public $status, $openModal, $statusUpdate, $testimonialId;
 

    public function mount(){
        Gate::authorize('Admin');
  
    }

    public function updatedStatus()
    {
        // Refresh testimonials whenever the status is updated
        $this->resetPage();
    }

    public function edit($id){
        $testimonial = Testimonial::findOrFail($id);

        if(!$testimonial){
            return session()->flash('error', 'This testimonial could not be found.');
        }else{
            $this->openModal = true;
            $this->testimonialId = $id;
            $this->statusUpdate = $testimonial->status;
        }
       
    }

    public function update($id){
        $testimonial = Testimonial::findOrFail($this->testimonialId);

        $validated = $this->validate([
            'statusUpdate' => 'required|in:Pending,WelcomePage,Approved,Hidden',
        ]);

        try {
            $testimonial->update([
                'status' => $validated['statusUpdate'],
            ]);

            session()->flash('success', 'Testimonial updated successfully');

        } catch (\Throwable $e) {
            Log::error('Failed to update testimonial ' . $e->getMessage());
            session()->flash('error', 'Failed to update testimonial');
        }

        $this->resetPage();
        $this->openModal = false;
        $this->testimonialId = null;


    }

    public function delete($id){
        $testimonial = Testimonial::findOrFail($id);

        $testimonial->delete();

        session()->flash('success', 'Testimonial deleted successfully');

        return redirect()->route('admin.testimonials');
    }

    public function render()
    {
        $query = Testimonial::query();

        if ($this->status) {
            $query->where('status', $this->status);
        }

        $testimonials = $query->latest()->paginate(9);

        return view('livewire.admin.admin-index-testimonials', [
            'testimonials' => $testimonials,
        ]);
    }
}
