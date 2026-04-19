<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programme_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_programme_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('learner_name');
            $table->string('class_level')->nullable();
            $table->string('school_name')->nullable();
            $table->string('exam_type')->nullable();
            $table->json('subjects');
            $table->text('weak_areas')->nullable();
            $table->text('recent_performance_notes')->nullable();
            $table->enum('lesson_mode', ['online', 'home']);
            $table->string('preferred_frequency');
            $table->string('preferred_duration');
            $table->decimal('price_quote', 12, 2)->nullable();
            $table->json('price_breakdown')->nullable();
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->json('preferred_days')->nullable();
            $table->json('preferred_times')->nullable();
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->text('parent_address')->nullable();
            $table->string('state')->nullable();
            $table->string('city_area')->nullable();
            $table->string('status')->default('pending');
            $table->string('source_page')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('programme_enquiry_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programme_enquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['assigned', 'accepted', 'declined', 'active', 'completed', 'cancelled'])->default('assigned');
            $table->text('admin_notes')->nullable();
            $table->text('tutor_notes')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programme_enquiry_assignments');
        Schema::dropIfExists('programme_enquiries');
    }
};
