<?php

namespace Tests\Feature;

use App\Livewire\Admin\ChurchLeadsManager;
use App\Livewire\Pages\ChurchLeadForm;
use App\Models\ChurchLead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class ChurchLeadWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function validForm(): array
    {
        return [
            'church_name' => 'Grace Community Church', 'contact_name' => 'Ada Example',
            'contact_role' => 'Administrator', 'email' => 'ada@example.org',
            'country_region' => 'Nigeria', 'contact_consent' => true,
        ];
    }

    private function fillForm($component)
    {
        foreach ($this->validForm() as $field => $value) $component->set($field, $value);
        return $component;
    }

    public function test_public_enquiry_requires_explicit_contact_consent_and_stores_marketing_separately(): void
    {
        $this->fillForm(Livewire::test(ChurchLeadForm::class))
            ->set('contact_consent', false)
            ->call('submit')
            ->assertHasErrors(['contact_consent']);
        $this->assertDatabaseCount('church_leads', 0);

        $this->fillForm(Livewire::test(ChurchLeadForm::class))
            ->set('marketing_consent', true)
            ->call('submit')
            ->assertSet('submitted', true);

        $lead = ChurchLead::firstOrFail();
        $this->assertTrue($lead->contact_consent);
        $this->assertTrue($lead->marketing_consent);
        $this->assertNotNull($lead->contact_consent_at);
        $this->assertNotNull($lead->marketing_consent_at);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $lead->id, 'type' => 'created']);
    }

    public function test_guest_and_non_admin_cannot_open_lead_management(): void
    {
        $this->get(route('admin.churchLeads'))->assertRedirect(route('login'));
        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($client)->get(route('admin.churchLeads'))->assertForbidden();
    }

    public function test_admin_can_assign_change_status_and_record_private_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $lead = ChurchLead::create($this->validForm());
        $this->actingAs($admin);

        Livewire::test(ChurchLeadsManager::class)
            ->call('open', $lead->id)
            ->set('statusInput', 'qualified')
            ->set('assigneeInput', (string) $admin->id)
            ->set('followUpInput', now()->addDays(2)->toDateString())
            ->call('updateWorkflow')
            ->set('note', 'Discussed a product demonstration.')
            ->call('addNote')
            ->assertHasNoErrors();

        $lead->refresh();
        $this->assertSame('qualified', $lead->status);
        $this->assertSame($admin->id, $lead->assigned_to);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $lead->id, 'type' => 'status_changed']);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $lead->id, 'type' => 'assignment_changed']);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $lead->id, 'type' => 'note', 'description' => 'Discussed a product demonstration.']);
    }

    public function test_public_form_rejects_honeypot_and_rate_limits_repeated_submissions(): void
    {
        $this->fillForm(Livewire::test(ChurchLeadForm::class))
            ->set('website', 'spam.example')
            ->call('submit')
            ->assertHasErrors(['website']);
        $this->assertDatabaseCount('church_leads', 0);

        RateLimiter::clear('church-lead:127.0.0.1');
        for ($i = 0; $i < 3; $i++) {
            $this->fillForm(Livewire::test(ChurchLeadForm::class)->set('email', "ada{$i}@example.org"))
                ->call('submit')->assertSet('submitted', true);
        }
        $this->fillForm(Livewire::test(ChurchLeadForm::class)->set('email', 'fourth@example.org'))
            ->call('submit')->assertHasErrors(['form']);
        $this->assertDatabaseCount('church_leads', 3);
    }

    public function test_admin_can_mark_a_likely_duplicate_without_deleting_either_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $existing = ChurchLead::create($this->validForm());
        $new = ChurchLead::create(array_merge($this->validForm(), ['church_name' => 'Grace Community Church - North', 'email' => 'other@example.org']));

        Livewire::actingAs($admin)->test(ChurchLeadsManager::class)
            ->call('open', $new->id)
            ->set('duplicateId', (string) $existing->id)
            ->call('markDuplicate')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('church_leads', 2);
        $this->assertDatabaseHas('church_leads', ['id' => $new->id, 'status' => 'duplicate', 'duplicate_of_id' => $existing->id]);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $new->id, 'type' => 'duplicate_marked']);
    }

    public function test_anonymization_removes_personal_details_from_activity_text_and_properties(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $lead = ChurchLead::create($this->validForm());
        $lead->activities()->create(['user_id' => $admin->id, 'type' => 'note', 'description' => 'Call Ada at ada@example.org', 'properties' => ['email' => 'ada@example.org']]);

        Livewire::actingAs($admin)->test(ChurchLeadsManager::class)
            ->call('open', $lead->id)
            ->call('anonymize')
            ->assertHasNoErrors();

        $lead->refresh();
        $this->assertSame('Anonymized contact', $lead->contact_name);
        $this->assertStringContainsString('@invalid.example', $lead->email);
        $this->assertDatabaseHas('church_lead_activities', [
            'church_lead_id' => $lead->id,
            'type' => 'note',
            'description' => 'Activity details removed during privacy anonymization.',
            'properties' => null,
        ]);
        $this->assertDatabaseHas('church_lead_activities', ['church_lead_id' => $lead->id, 'type' => 'anonymized']);
    }
}
