<?php

namespace App\Livewire;

use App\Models\ServiceItem;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class HeroCarousel extends Component
{
    public $items;

    public function mount()
    {
        $this->items = Cache::remember('hero.service_items.arrays-v3', 3600, function () {
            $dbItems = ServiceItem::where('is_active', true)
                ->where('display_position', '>', 0)
                ->orderBy('display_position')
                ->get();

            if ($dbItems->isNotEmpty()) {
                return $dbItems->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'slug' => $item->slug,
                        'image_url' => $this->imageUrl($item->image_path),
                        'description' => $item->description,
                        'target' => $item->target,
                    ];
                })->toArray();
            }

            // fallback items
            return array_map(function (array $item): array {
                $item['image_url'] = $this->imageUrl($item['image_path']);

                return $item;
            }, [
                [
                    'name' => 'Home Tutoring',
                    'slug' => 'home-tutoring',
                    'image_path' => '/images/b-home-tutoring.jpg',
                    'description' => 'Unlock personalized academic support from the comfort of your home.',
                    'target' => 'tutor_request',
                ],
                [
                    'name' => 'Coding Classes',
                    'slug' => 'coding',
                    'image_path' => '/images/coding-banner2.jpeg',
                    'description' => 'Master modern stacks with expert guidance.',
                    'target' => 'tutor_request',
                ],
                [
                    'name' => 'School Clubs',
                    'slug' => 'clubs',
                    'image_path' => '/images/robotics.jpg',
                    'description' => 'Expert club instructors for coding, music, chess, etc.',
                    'target' => 'institutions',
                ],
            ]);
        });
    }

    private function imageUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return asset('images/MephEd.png');
        }

        if (filter_var($path, FILTER_VALIDATE_URL) && in_array(parse_url($path, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        return asset(str_starts_with($normalized, 'images/') || str_starts_with($normalized, 'storage/')
            ? $normalized
            : 'storage/'.$normalized);
    }

    public function render()
    {
        return view('livewire.hero-carousel', ['items' => $this->items]);
    }
}
