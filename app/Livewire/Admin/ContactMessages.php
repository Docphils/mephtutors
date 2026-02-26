<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Contact;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Contact Messages - MephEd Admin')]
class ContactMessages extends Component
{
    use WithPagination;

    public $selectedMessage;
    public $sortField = 'created_at'; 
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $search = '';
    public $show;

    protected $listeners = ['messageRead' => '$refresh'];

    /**
     * Sort by a specific field
     */
    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortField = $field;
    }

    /**
     * Show a specific message
     */
    public function showMessage($id)
    {
        $this->show = true;
        $this->selectedMessage = Contact::find($id);

        // Mark the message as read
        if ($this->selectedMessage && !$this->selectedMessage->is_read) {
            $this->selectedMessage->update(['is_read' => true]);
            $this->dispatch('messageRead');
        }
    }

    public function render()
    {
        $messages = Contact::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.contact-messages', [
            'messages' => $messages,
        ]);
    }
}