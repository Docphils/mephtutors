<?php

namespace Tests\Feature;

use App\Livewire\Admin\IncomeManager;
use App\Livewire\Admin\BootcampManager;
use App\Models\Cohort;
use App\Models\Crm;
use App\Models\Enrollee;
use App\Models\IncomeTransaction;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use App\Services\IncomeLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;
use Tests\TestCase;

class IncomeManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_page_is_admin_only_and_manual_income_can_be_recorded(): void
    {
        $this->get(route('admin.income.index'))->assertRedirect(route('login'));

        $client = User::factory()->create(['role' => 'client', 'email_verified_at' => now()]);
        $this->actingAs($client)->get(route('admin.income.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($admin)->get(route('admin.income.index'))
            ->assertOk()
            ->assertSee('Incoming transactions')
            ->assertSee('Outgoing tutor payouts remain');

        Livewire::actingAs($admin)->test(IncomeManager::class)
            ->assertSee('wire:click="openForm"', false)
            ->call('openForm')
            ->assertSee('Record manual income')
            ->set('amount', '2500.50')
            ->set('description', 'Consultation payment')
            ->set('payerName', 'Example Client')
            ->set('manualMethod', 'bank_transfer')
            ->set('reference', 'BANK-REF-100')
            ->set('receivedAt', now()->format('Y-m-d\TH:i'))
            ->set('notes', 'First instalment')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSee('Consultation payment');

        $transaction = IncomeTransaction::query()->sole();
        $this->assertSame('2500.50', $transaction->amount);
        $this->assertSame('bank_transfer', $transaction->method);
        $this->assertSame('manual', $transaction->source_type);
        $this->assertSame($admin->id, $transaction->recorded_by);
        $this->assertSame('BANK-REF-100', $transaction->reference);
    }

    public function test_verified_paystack_income_is_idempotently_recorded(): void
    {
        $ledger = app(IncomeLedger::class);
        $transaction = [
            'reference' => 'paystack-income-100',
            'amount' => 375000,
            'currency' => 'NGN',
            'status' => 'success',
            'paid_at' => '2026-11-08T10:15:00Z',
            'customer' => ['first_name' => 'Ada', 'last_name' => 'Example'],
        ];

        $ledger->recordPaystack($transaction, 'booking', 45, 'Booking payment #45');
        $ledger->recordPaystack($transaction, 'booking', 45, 'Booking payment #45');

        $this->assertSame(1, IncomeTransaction::query()->count());
        $income = IncomeTransaction::query()->sole();
        $this->assertSame('3750.00', $income->amount);
        $this->assertSame('paystack', $income->method);
        $this->assertSame('paystack-income-100', $income->gateway_reference);
        $this->assertSame('Ada Example', $income->payer_name);
        $this->assertSame('booking', $income->source_type);
        $this->assertNotNull($income->received_at);
    }

    public function test_successful_paystack_webhook_records_institution_income_once(): void
    {
        Config::set('services.paystack.secret', 'sk_test_income');
        $client = User::factory()->create(['role' => 'client']);
        $service = Service::query()->create([
            'name' => 'Institution Service',
            'slug' => 'institution-service',
            'target' => 'institutions',
        ]);
        $serviceItem = ServiceItem::query()->create([
            'service_id' => $service->id,
            'name' => 'School Programme',
            'slug' => 'school-programme',
            'target' => 'institutions',
        ]);
        $crm = Crm::query()->create([
            'user_id' => $client->id,
            'institution_name' => 'Example School',
            'institution_address' => 'Lagos',
            'service_item_id' => $serviceItem->id,
            'engagement_type' => 'contract',
            'quote_amount' => 4500,
            'payment_status' => 'pending',
        ]);
        $payload = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'crm-income-reference',
                'amount' => 450000,
                'currency' => 'NGN',
                'status' => 'success',
                'paid_at' => '2026-11-08T10:15:00Z',
                'metadata' => [
                    'app' => 'mephed',
                    'payment_for' => 'crm',
                    'crm_id' => $crm->id,
                ],
            ],
        ];
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha512', $body, 'sk_test_income');

        foreach (range(1, 2) as $_) {
            $this->call('POST', route('paystack.webhook'), [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_PAYSTACK_SIGNATURE' => $signature,
            ], $body)->assertOk();
        }

        $this->assertSame('paid', $crm->fresh()->payment_status);
        $this->assertSame(1, IncomeTransaction::query()
            ->where('gateway_reference', 'crm-income-reference')
            ->count());
    }

    public function test_backfill_imports_paid_bootcamp_enrolments_without_duplicate_rows(): void
    {
        $service = Service::query()->create([
            'name' => 'Bootcamp Service',
            'slug' => 'bootcamp-service',
            'target' => 'bootcamp',
        ]);
        $serviceItem = ServiceItem::query()->create([
            'service_id' => $service->id,
            'name' => 'Coding Bootcamp',
            'slug' => 'coding-bootcamp',
            'target' => 'bootcamp',
        ]);
        $cohort = Cohort::query()->create([
            'service_id' => $service->id,
            'service_item_id' => $serviceItem->id,
            'name' => 'November Coding Cohort',
            'code' => 'NOV-CODING-2026',
            'start_date' => '2026-11-01',
            'fee' => 85000,
        ]);
        $paidEnrollee = Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Paid Learner',
            'email' => 'paid@example.com',
            'phone' => '08000000000',
            'address' => 'Lagos',
            'status' => 'confirmed',
            'confirmed_at' => '2026-11-08 10:00:00',
            'meta' => [
                'payment_status' => 'paid',
                'payment_reference' => 'bootcamp-payment-100',
            ],
        ]);
        Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Unpaid Learner',
            'email' => 'unpaid@example.com',
            'phone' => '08000000001',
            'address' => 'Lagos',
            'meta' => ['payment_status' => 'pending'],
        ]);

        $migration = require database_path('migrations/2026_11_09_100000_backfill_paid_bootcamp_income.php');
        $migration->up();
        $migration->up();

        $income = IncomeTransaction::query()
            ->where('source_type', 'enrollee')
            ->where('source_id', $paidEnrollee->id)
            ->sole();

        $this->assertSame('85000.00', $income->amount);
        $this->assertSame('paystack', $income->method);
        $this->assertSame('bootcamp-payment-100', $income->gateway_reference);
        $this->assertSame('2026-11-08 10:00:00', $income->received_at?->format('Y-m-d H:i:s'));
        $this->assertSame(1, IncomeTransaction::query()->where('source_type', 'enrollee')->count());
    }

    public function test_legacy_backfill_recovers_confirmed_enrollees_when_payment_metadata_was_overwritten(): void
    {
        $service = Service::query()->create([
            'name' => 'Bootcamp Service',
            'slug' => 'legacy-bootcamp-service',
            'target' => 'bootcamp',
        ]);
        $serviceItem = ServiceItem::query()->create([
            'service_id' => $service->id,
            'name' => 'Legacy Coding Bootcamp',
            'slug' => 'legacy-coding-bootcamp',
            'target' => 'bootcamp',
        ]);
        $cohort = Cohort::query()->create([
            'service_id' => $service->id,
            'service_item_id' => $serviceItem->id,
            'name' => 'Legacy Cohort',
            'code' => 'LEGACY-BOOTCAMP-2025',
            'start_date' => '2025-01-01',
            'fee' => 75000,
        ]);
        $confirmedEnrollee = Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Confirmed Learner',
            'email' => 'confirmed@example.com',
            'phone' => '08000000002',
            'address' => 'Lagos',
            'status' => 'completed',
            'meta' => ['notes' => 'Old admin edit replaced the Paystack metadata.'],
        ]);
        $paidEnrollee = Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Paid Learner',
            'email' => 'paid@example.com',
            'phone' => '08000000003',
            'address' => 'Lagos',
            'status' => 'pending',
            'meta' => [
                'payment_status' => 'paid',
                'payment_reference' => 'legacy-paid-reference',
            ],
        ]);
        Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Pending Learner',
            'email' => 'pending@example.com',
            'phone' => '08000000004',
            'address' => 'Lagos',
            'status' => 'pending',
            'meta' => ['payment_status' => 'pending'],
        ]);

        $migration = require database_path('migrations/2026_11_09_110000_backfill_legacy_confirmed_bootcamp_income.php');
        $migration->up();
        $migration->up();

        $unverifiedIncome = IncomeTransaction::query()
            ->where('source_type', 'enrollee')
            ->where('source_id', $confirmedEnrollee->id)
            ->sole();
        $verifiedIncome = IncomeTransaction::query()
            ->where('source_type', 'enrollee')
            ->where('source_id', $paidEnrollee->id)
            ->sole();

        $this->assertSame('unrecorded', $unverifiedIncome->method);
        $this->assertNull($unverifiedIncome->gateway_reference);
        $this->assertStringContainsString('could not be verified', $unverifiedIncome->notes);
        $this->assertSame('paystack', $verifiedIncome->method);
        $this->assertSame('legacy-paid-reference', $verifiedIncome->gateway_reference);
        $this->assertSame(2, IncomeTransaction::query()->where('source_type', 'enrollee')->count());
    }

    public function test_admin_enrollee_edits_preserve_existing_payment_metadata(): void
    {
        $service = Service::query()->create([
            'name' => 'Bootcamp Service',
            'slug' => 'metadata-bootcamp-service',
            'target' => 'bootcamp',
        ]);
        $serviceItem = ServiceItem::query()->create([
            'service_id' => $service->id,
            'name' => 'Metadata Bootcamp',
            'slug' => 'metadata-bootcamp',
            'target' => 'bootcamp',
        ]);
        $cohort = Cohort::query()->create([
            'service_id' => $service->id,
            'service_item_id' => $serviceItem->id,
            'name' => 'Metadata Cohort',
            'code' => 'METADATA-BOOTCAMP-2026',
            'start_date' => '2026-11-01',
            'fee' => 85000,
        ]);
        $enrollee = Enrollee::query()->create([
            'cohort_id' => $cohort->id,
            'name' => 'Learner With Payment',
            'email' => 'learner@example.com',
            'phone' => '08000000005',
            'address' => 'Lagos',
            'status' => 'confirmed',
            'meta' => [
                'payment_status' => 'paid',
                'payment_reference' => 'preserve-this-reference',
                'notes' => 'Existing private note',
            ],
        ]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Livewire::actingAs($admin)->test(BootcampManager::class)
            ->call('editEnrollee', $enrollee->id)
            ->set('enrollee_notes', 'Updated private note')
            ->call('saveEnrollee')
            ->assertHasNoErrors();

        $this->assertSame('paid', $enrollee->fresh()->meta['payment_status']);
        $this->assertSame('preserve-this-reference', $enrollee->fresh()->meta['payment_reference']);
        $this->assertSame('Updated private note', $enrollee->fresh()->meta['notes']);
    }
}
