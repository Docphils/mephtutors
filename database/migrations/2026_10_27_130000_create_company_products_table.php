<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 140);
            $table->string('slug', 160)->unique();
            $table->string('category', 40)->default('software')->index();
            $table->string('release_status', 40)->default('available')->index();
            $table->string('summary', 500);
            $table->text('description')->nullable();
            $table->string('website_url', 2048)->nullable();
            $table->string('cta_label', 60)->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('company_products')->insert([
            [
                'name' => 'Zaramed', 'slug' => 'zaramed', 'category' => 'software', 'release_status' => 'available',
                'summary' => 'A smart school management and CBT platform designed to help schools simplify administration, improve assessments, and strengthen communication across their operations.',
                'website_url' => 'https://www.zaramed.ng', 'cta_label' => 'Visit Zaramed', 'image_path' => 'images/zaramed.png',
                'is_featured' => true, 'is_published' => true, 'display_order' => 1, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'name' => 'Solar Sapient', 'slug' => 'solar-sapient', 'category' => 'software', 'release_status' => 'available',
                'summary' => 'An intelligent solar education, advisory, and audit platform built to help users make better energy decisions with more clarity, confidence, and structure.',
                'website_url' => 'https://solar-sapient.mephed.ng', 'cta_label' => 'Visit Solar Sapient', 'image_path' => 'images/solar-sapient.png',
                'is_featured' => false, 'is_published' => true, 'display_order' => 2, 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_products');
    }
};
