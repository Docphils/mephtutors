<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_live_metrics_quick_links_and_recent_activity(): void
    {
        $admin = User::factory()->create([
            'name' => 'Avery Admin',
            'role' => 'admin',
            'email_verified_at' => now(),
            'created_at' => now(),
        ]);
        User::factory()->create(['role' => 'client', 'created_at' => now()]);
        User::factory()->create(['role' => 'tutor', 'created_at' => now()]);
        Contact::query()->create([
            'name' => 'Recent learner',
            'email' => 'learner@example.test',
            'phone' => '08000000000',
            'message' => 'I need help finding a tutor.',
            'is_read' => false,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Welcome back, Avery.')
            ->assertSee('Total users')
            ->assertSee('Earned payments')
            ->assertSee('Operational pipeline')
            ->assertSee('Signups & bookings', false)
            ->assertSee('Quick links')
            ->assertSee('Recent learner')
            ->assertSee('I need help finding a tutor.')
            ->assertSee(route('admin.ebooks.orders'), false)
            ->assertSee(route('admin.blog.index'), false)
            ->assertSee('3 joined in the last 7 days');
    }

    public function test_admin_dashboard_remains_restricted_to_administrators(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $client = User::factory()->create([
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($client)->get(route('admin.dashboard'))->assertForbidden();
    }
}
