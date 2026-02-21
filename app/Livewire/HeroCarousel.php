<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ServiceItem;
use Illuminate\Support\Facades\Cache;

class HeroCarousel extends Component
{
    public $items;

    public function mount()
    {
        $this->items = Cache::remember('hero.service_items', 3600, function () {
            // 1. Try to fetch active items from DB
            $dbItems = ServiceItem::where('is_active', true)
                ->where('display_position', '>', 0)
                ->orderBy('display_position')
                ->get();

            // 2. If DB has items, return them (converting to objects for consistency)
            if ($dbItems->isNotEmpty()) {
                return $dbItems->map(function ($item) {
                    return (object) [
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'image_path' => $item->image_path,
                        'description' => $item->description,
                    ];
                })->toArray();
            }

            // 3. Fallback to defaults if DB is empty
            return [
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
                    'description' => 'WAEC, NECO, JAMB, BECE, IELTS; structured prep that works.',
                ],
            ];
        });
    }

    public function render()
    {
        return view('livewire.hero-carousel', [
            'items' => $this->items,
        ]);
    }
}