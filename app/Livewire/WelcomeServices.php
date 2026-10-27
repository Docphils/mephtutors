<?php

namespace App\Livewire;

use App\Models\ServiceItem;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class WelcomeServices extends Component
{
    public $items;

    public function mount()
    {
        $this->items = Cache::remember('welcome.service_items.arrays-v2', 3600, function () {
            $dbItems = ServiceItem::where('is_active', true)
                ->where('display_position', '!=', 0)
                ->orderBy('display_position')
                ->get();

            if ($dbItems->isEmpty()) {
                return [
                    ['name' => 'Home Tutoring', 'slug' => 'home-tutoring', 'image_path' => '/images/b-home-tutoring.jpg', 'description' => 'Unlock personalized academic support from the comfort of your home.', 'target' => 'tutor_request'],
                    ['name' => 'Coding Classes', 'slug' => 'coding', 'image_path' => '/images/coding-banner2.jpeg', 'description' => 'Master modern stacks with expert guidance.', 'target' => 'tutor_request'],
                    ['name' => 'School Clubs', 'slug' => 'clubs', 'image_path' => '/images/robotics.jpg', 'description' => 'Expert club instructors for coding, music, chess, etc.', 'target' => 'institutions'],
                ];
            }

            return $dbItems->map(function ($item) {
                return [
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'image_path' => $item->image_path,
                    'description' => $item->description,
                    'target' => $item->target ?? 'tutor_request',
                ];
            })->all();
        });
    }

    public function render()
    {
        return view('livewire.welcome-services', ['items' => $this->items]);
    }
}
