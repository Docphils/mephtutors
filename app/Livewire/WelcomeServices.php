<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Cache;

class WelcomeServices extends Component
{
    public $items;

    public function mount()
    {
        $this->items = Cache::remember('welcome.service_items', 3600, function () {
            $dbItems = ServiceItem::where('is_active', true)
                ->where('display_position', '!=', 0)
                ->orderBy('display_position')
                ->get();

            // fallback if DB is empty
            if ($dbItems->isEmpty()) {
                return collect([
                    (object)[
                        'name' => 'Home Tutoring',
                        'slug' => 'home-tutoring',
                        'image_path' => '/images/b-home-tutoring.jpg',
                        'description' => 'Unlock personalized academic support from the comfort of your home.',
                    ],
                    (object)[
                        'name' => 'Coding Classes',
                        'slug' => 'coding',
                        'image_path' => '/images/coding-banner2.jpeg',
                        'description' => 'Master modern stacks with expert guidance.',
                    ],
                    (object)[
                        'name' => 'Exam Prep',
                        'slug' => 'exam-prep',
                        'image_path' => '/images/b-waec.jpeg',
                        'description' => 'Structured exam prep that works for WAEC, JAMB, and more.',
                    ],
                ]);
            }

            return $dbItems;
        });
    }

    public function render()
    {
        return view('livewire.welcome-services', [
            'items' => $this->items,
        ]);
    }
}