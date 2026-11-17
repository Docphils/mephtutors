<?php

namespace App\Livewire;

use App\Models\ServiceItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class WelcomeServices extends Component
{
    public function render(): View
    {
        // Cards are display data, so rebuild the view from the server cache on every
        // request rather than hydrating them from a public Livewire property.
        $items = Cache::remember('welcome.service_items.cards-v3', 3600, function (): array {
            $dbItems = ServiceItem::where('is_active', true)
                ->where('display_position', '!=', 0)
                ->orderBy('display_position')
                ->get();

            if ($dbItems->isEmpty()) {
                return [
                    ['name' => 'Home Tutoring', 'slug' => 'home-tutoring', 'image_url' => asset('images/b-home-tutoring.jpg'), 'description' => 'Unlock personalized academic support from the comfort of your home.', 'target' => 'tutor_request'],
                    ['name' => 'Coding Classes', 'slug' => 'coding', 'image_url' => asset('images/coding-banner2.jpeg'), 'description' => 'Master modern stacks with expert guidance.', 'target' => 'tutor_request'],
                    ['name' => 'School Clubs', 'slug' => 'clubs', 'image_url' => asset('images/robotics.jpg'), 'description' => 'Expert club instructors for coding, music, chess, etc.', 'target' => 'institutions'],
                ];
            }

            return $dbItems->map(function (ServiceItem $item): array {
                return [
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'image_url' => $item->image_url,
                    'description' => $item->description,
                    'target' => $item->target ?? 'tutor_request',
                ];
            })->all();
        });

        return view('livewire.welcome-services', ['items' => $items]);
    }
}
