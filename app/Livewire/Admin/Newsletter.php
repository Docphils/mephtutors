<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Newsletter as NewsletterModel;
use App\Jobs\SendNewsletterJob;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Newsletter Campaign Manager')]
class Newsletter extends Component
{
    use WithFileUploads, WithPagination;

    protected $paginationTheme = 'tailwind';

    public $subject;
    public $title;
    public $body = '';
    public $body2;
    public $recipients = 'All';
    public $attachment;

    public $showForm = false;
    public $editingId = null;
    public $selectedCampaign = null;
    public $status;

    protected $listeners = ['createNewsletter' => 'create'];

    protected function rules()
    {
        return [
            'subject' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'body2' => 'nullable|string',
            'recipients' => 'required|in:All,Admins,Clients,Tutors,TestTutor,TutorsWithProfile,TutorsWithoutProfile',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,svg,mp4',
        ];
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    // Update viewDetails method
public function viewDetails($id)
{
    $campaign = NewsletterModel::findOrFail($id);
    
    if ($campaign->status === 'Draft') {
        $this->editingId = $campaign->id;
        $this->subject = $campaign->subject;
        $this->title = $campaign->title;
        $this->body = $campaign->body;
        $this->body2 = $campaign->body2;
        $this->recipients = $campaign->recipients;
        $this->status = $campaign->status;
        $this->showForm = true;
        $this->selectedCampaign = null;
    } else {
        $this->selectedCampaign = $campaign;
        $this->showForm = false;
        $this->editingId = null;
    }
}

    // Update saveDraft to handle both Create and Update
    public function saveDraft()
    {
        $data = $this->validate();

        NewsletterModel::updateOrCreate(
            ['id' => $this->editingId],
            [
                'subject' => $this->subject,
                'title' => $this->title,
                'body' => $this->body,
                'body2' => $this->body2,
                'recipients' => $this->recipients,
                'attachments' => $this->attachment ? $this->storeAttachment() : ($this->editingId ? NewsletterModel::find($this->editingId)->attachments : null),
                'status' => 'Draft',
                'created_by' => auth()->id(),
            ]
        );

        $this->resetForm();
        $this->showForm = false;
        session()->flash('success', $this->editingId ? 'Draft updated.' : 'Draft saved.');
    }

    public function send()
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {

            $newsletter = NewsletterModel::create([
                ...$data,
                'attachments' => $this->storeAttachment(),
                'status' => 'Draft',
                'created_by' => auth()->id(),
            ]);

            SendNewsletterJob::dispatch($newsletter);
        });

        $this->resetForm();
        $this->showForm = false;
        session()->flash('success', 'Campaign queued for sending.');
    }

    public function resend($id)
    {
        $newsletter = NewsletterModel::findOrFail($id);

        SendNewsletterJob::dispatch($newsletter);

        session()->flash('success', 'Campaign re-queued.');
    }

    protected function storeAttachment()
    {
        return $this->attachment
            ? $this->attachment->store('newsletter-attachments', 'public')
            : null;
    }

    protected function resetForm()
    {
        $this->reset([
            'subject',
            'title',
            'body',
            'body2',
            'recipients',
            'attachment',
            'editingId'
        ]);
    }

    public function render()
    {
        return view('livewire.admin.newsletter', [
            'campaigns' => NewsletterModel::latest()->paginate(10)
        ]);
    }
}