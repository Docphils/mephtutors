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
        Schema::create('tutor_requests', function (Blueprint $table) {
        $table->id();

        // Client
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->boolean('is_for_self')->default(true);
        $table->json('learners')->nullable();

        // Service
        $table->foreignId('service_item_id')->constrained()->cascadeOnDelete();

        $table->foreignId('level_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('exam_type_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('curriculum', ['British', 'French', 'Nigerian', 'Blended', 'N/A'])->default('N/A');

        // Delivery
        $table->enum('delivery_mode', ['online', 'offline', 'hybrid']);
        $table->enum('session_type', ['individual', 'group']);

        // Scheduling
        $table->json('preferred_days')->nullable(); // JSON later
        $table->integer('duration_per_session')->nullable(); // in minutes

        // Budget
        $table->decimal('budget_min', 10, 2)->nullable();
        $table->decimal('budget_max', 10, 2)->nullable();

        // Location (for offline)
        $table->text('lesson_address')->nullable();

        // Preferences
        $table->enum('preferred_tutor_gender', ['male', 'female', 'any'])->default('any');
        $table->text('additional_notes')->nullable();
        $table->json('subjects')->nullable();

        // System
        $table->enum('status', [
            'pending',
            'reviewing',
            'matched',
            'in_progress',
            'completed',
            'cancelled'
        ])->default('pending');

        $table->timestamp('matched_at')->nullable();
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
        Schema::dropIfExists('tutor_requests');
    }
};
