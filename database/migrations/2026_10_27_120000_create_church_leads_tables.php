<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('church_leads', function (Blueprint $table) {
            $table->id();
            $table->string('church_name', 160);
            $table->string('contact_name', 120);
            $table->string('contact_role', 120);
            $table->string('email', 190)->index();
            $table->string('phone', 40)->nullable();
            $table->string('country_region', 120);
            $table->string('congregation_size', 40)->nullable();
            $table->string('current_records_method', 60)->nullable();
            $table->json('interests')->nullable();
            $table->string('timeframe', 40)->nullable();
            $table->string('preferred_contact_method', 20)->nullable();
            $table->string('source', 160)->nullable();
            $table->text('enquiry')->nullable();
            $table->boolean('contact_consent')->default(false);
            $table->timestamp('contact_consent_at')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('marketing_consent_at')->nullable();
            $table->string('status', 40)->default('new')->index();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('follow_up_at')->nullable()->index();
            $table->string('not_proceeding_reason', 120)->nullable();
            $table->foreignId('duplicate_of_id')->nullable()->constrained('church_leads')->nullOnDelete();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('church_lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->text('description')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index(['church_lead_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('church_lead_activities');
        Schema::dropIfExists('church_leads');
    }
};
