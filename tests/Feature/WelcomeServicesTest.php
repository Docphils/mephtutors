<?php

namespace Tests\Feature;

use App\Livewire\WelcomeServices;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Tests\TestCase;

class WelcomeServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_cards_keep_their_details_and_links_after_a_livewire_refresh(): void
    {
        $service = Service::query()->create(['name' => 'Programs', 'slug' => 'programs']);
        $items = [
            ['name' => 'School Robotics', 'slug' => 'school-robotics', 'target' => 'institutions', 'display_position' => 3, 'image_path' => 'https://example.com/robotics.jpg'],
            ['name' => 'Piano Lessons', 'slug' => 'piano-lessons', 'target' => 'tutor_request', 'display_position' => 1, 'image_path' => '/images/piano.jpg'],
            ['name' => 'Coding Camp', 'slug' => 'coding-camp', 'target' => 'bootcamp', 'display_position' => 2, 'image_path' => 'service-items/coding.jpg'],
            ['name' => 'Inactive Program', 'slug' => 'inactive-program', 'display_position' => 4, 'is_active' => false],
            ['name' => 'Hidden Program', 'slug' => 'hidden-program', 'display_position' => 0],
        ];
        foreach ($items as $item) {
            ServiceItem::query()->create(['service_id' => $service->id, ...$item]);
        }

        $component = Livewire::test(WelcomeServices::class);
        $this->assertArrayNotHasKey('items', $component->snapshot['data']);

        $component->call('$refresh')
            ->assertSeeInOrder(['Piano Lessons', 'Coding Camp', 'School Robotics'])
            ->assertSee(route('apply.tutor', ['serviceItem' => 'piano-lessons']))
            ->assertSee(route('apply.bootcamp', ['serviceItem' => 'coding-camp']))
            ->assertSee(route('apply.crm', ['serviceItem' => 'school-robotics']))
            ->assertSee(asset('images/piano.jpg'))
            ->assertSee(asset('storage/service-items/coding.jpg'))
            ->assertSee('https://example.com/robotics.jpg')
            ->assertDontSee('Inactive Program')
            ->assertDontSee('Hidden Program');
        $this->assertArrayNotHasKey('items', $component->snapshot['data']);
    }

    public function test_old_integer_card_snapshots_are_ignored_on_refresh(): void
    {
        $component = Livewire::test(WelcomeServices::class);

        // Simulate an open page carrying the retired public items property.
        $snapshot = $component->snapshot;
        $snapshot['data']['items'] = [[17, 18], ['s' => 'arr']];
        unset($snapshot['checksum']);
        $snapshot['checksum'] = Checksum::generate($snapshot);
        $component->snapshot = $snapshot;

        $component->call('$refresh')
            ->assertSee('Explore Our Programs')
            ->assertSee(route('apply.tutor', ['serviceItem' => 'home-tutoring']))
            ->assertSee(route('apply.crm', ['serviceItem' => 'clubs']));
        $this->assertArrayNotHasKey('items', $component->snapshot['data']);
    }

    public function test_legacy_integer_cache_entries_do_not_break_fallback_programs(): void
    {
        Cache::put('welcome.service_items.arrays-v2', [17, 18], 3600);

        Livewire::test(WelcomeServices::class)
            ->call('$refresh')
            ->assertSee(asset('images/b-home-tutoring.jpg'))
            ->assertSee(asset('images/coding-banner2.jpeg'))
            ->assertSee(asset('images/robotics.jpg'))
            ->assertDontSee('/storage//images/');
    }
}
