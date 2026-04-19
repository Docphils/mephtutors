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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_programmes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('service_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tagline')->nullable();
            $table->text('summary')->nullable();
            $table->text('overview')->nullable();
            $table->text('who_it_is_for')->nullable();
            $table->text('what_parents_can_expect')->nullable();
            $table->string('starting_from_text')->nullable();
            $table->text('pricing_note')->nullable();
            $table->string('renewability_note')->nullable();
            $table->json('frequency_options')->nullable();
            $table->json('duration_options')->nullable();
            $table->json('mode_options')->nullable();
            $table->json('subject_options')->nullable();
            $table->unsignedTinyInteger('max_selectable_subjects')->default(6);
            $table->json('pricing_matrix')->nullable();
            $table->json('faq_items')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_programmes');
        Schema::dropIfExists('site_settings');
    }
};
