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
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('shown_on_welcome')->default(true);
            $table->integer('display_position')->default(0);

            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->enum('target', ['tutor_request', 'institutions', 'bootcamp'])->default('tutor_request');
            $table->boolean('has_subjects')->default(true);
            $table->boolean('requires_curriculum')->default(false);
            $table->boolean('requires_level')->default(true);
            $table->boolean('requires_exam_type')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
