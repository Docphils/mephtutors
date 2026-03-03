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
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_item_id')->constrained()->cascadeOnDelete();

            // public identity
            $table->string('name'); // Frontend Dev Cohort 3
            $table->string('code')->unique(); // FE-2026-03

            // scheduling
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // delivery
            $table->enum('mode', ['online', 'physical', 'hybrid'])->default('online');
            $table->string('location')->nullable();

            // management
            $table->integer('capacity')->nullable();
            $table->decimal('fee', 10, 2)->nullable();

            // lifecycle
            $table->enum('status', [
                'draft',
                'open',
                'running',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
