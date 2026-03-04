<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cohort;
use App\Models\Enrollee;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Mail;
use App\Mail\BootcampSubmissionNotification;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.visitor')]
#[Title('MephEd Learning - Enroll')]
class EnrollmentForm extends Component
{
    public $name;
    public $email;
    public $phone;
    public $address;
    public $service_item_id;
    public $cohort_id;
    public $service_item_name;

    public $serviceItems = [];
    public $availableCohorts = [];
    public $bootcampServiceIds = [];
    public $serviceItemSlug = null;

    public function mount($serviceItem = null, $serviceItemSlug = null)
    {
        if ($serviceItem instanceof ServiceItem) {
            $this->serviceItemSlug = $serviceItem->slug;
        } elseif (is_string($serviceItem) && $serviceItem !== '') {
            $this->serviceItemSlug = $serviceItem;
        } else {
            $this->serviceItemSlug = $serviceItemSlug;
        }

        $this->bootcampServiceIds = Service::where('target', 'bootcamp')
            ->where('is_active', true)
            ->pluck('id')
            ->all();

        if (empty($this->bootcampServiceIds)) {
            return;
        }

        $this->serviceItems = ServiceItem::whereIn('service_id', $this->bootcampServiceIds)
            ->where('target', 'bootcamp')
            ->where('is_active', true)
            ->orderBy('display_position')
            ->orderBy('name')
            ->get();

        if ($this->serviceItemSlug) {
            $item = $this->serviceItems->firstWhere('slug', $this->serviceItemSlug);
            if ($item) {
                $this->service_item_id = $item->id;
                $this->service_item_name = $item->name;
                $this->loadAvailableCohorts();
            }
        }
    }

    public function updatedServiceItemId()
    {
        $this->cohort_id = null;
        $this->loadAvailableCohorts();
    }

    private function loadAvailableCohorts()
    {
        if (empty($this->bootcampServiceIds) || !$this->service_item_id) {
            $this->availableCohorts = [];
            return;
        }

        $this->availableCohorts = Cohort::whereIn('service_id', $this->bootcampServiceIds)
            ->where('service_item_id', $this->service_item_id)
            ->whereIn('status', ['open', 'running'])
            ->orderBy('start_date')
            ->get();
    }

    protected $rules = [
        'service_item_id' => 'required|exists:service_items,id',
        'cohort_id' => 'required|exists:cohorts,id',
        'name' => 'required|string|min:3',
        'email' => 'required|email|max:150',
        'phone' => 'required|string|min:6|max:20',
        'address' => 'required|string|max:255',
    ];

    public function save()
    {
        if (empty($this->bootcampServiceIds)) {
            session()->flash('error', 'Bootcamp is not configured yet. Please try again later.');
            return;
        }

        $this->validate();

        $cohort = Cohort::with(['service', 'serviceItem'])
            ->where('id', $this->cohort_id)
            ->whereIn('service_id', $this->bootcampServiceIds)
            ->where('service_item_id', $this->service_item_id)
            ->whereHas('service', fn ($q) => $q->where('target', 'bootcamp'))
            ->whereHas('serviceItem', fn ($q) => $q->where('target', 'bootcamp'))
            ->whereIn('status', ['open', 'running'])
            ->first();

        if (!$cohort) {
            $this->addError('cohort_id', 'Please select a valid open cohort.');
            return;
        }

        $registrant = [
            'service' => $cohort->service->name ?? null,
            'service_item' => $cohort->serviceItem->name ?? null,
            'cohort' => $cohort->name,
            'cohort_code' => $cohort->code,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
        ];

        Enrollee::create([
            'cohort_id' => $cohort->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'status' => 'pending',
            'is_read' => false,
            'meta' => [
                'source' => 'bootcamp-landing-page',
                'service_item_id' => $this->service_item_id,
            ],
        ]);

        try {
            Mail::to('admin@mephed.ng')->send(new BootcampSubmissionNotification($registrant));
        } catch (\Exception $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
        }

        session()->flash('success', 'Registration received. Join our WhatsApp Community to stay updated.');

        $this->reset(['name', 'email', 'phone', 'address', 'service_item_id', 'cohort_id']);
        $this->availableCohorts = [];
    }

    public function render()
    {
        $description = 'Enroll to learn in-demand educational and technology skills with MephEd Learning bootcamps.';

        return view('livewire.enrollment-form')
            ->title('MephEd Learning - Enroll')
            ->layoutData([
                'metaDescription' => $description,
                'canonicalUrl' => $this->serviceItemSlug
                    ? route('apply.bootcamp', ['serviceItem' => $this->serviceItemSlug])
                    : route('bootcamp'),
                'ogType' => 'website',
                'ogImage' => asset('images/banner.jpg'),
            ]);
    }
}
