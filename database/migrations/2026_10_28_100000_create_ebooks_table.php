<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebooks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 200);
            $table->string('subtitle', 300)->nullable();
            $table->string('author_name', 160);
            $table->string('publisher_name', 160)->default('MephEd');
            $table->unsignedInteger('price_kobo');
            $table->char('currency', 3)->default('NGN');
            $table->string('cover_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('free_chapter_path')->nullable();
            $table->longText('free_chapter_content')->nullable();
            $table->string('cheat_sheet_path')->nullable();
            $table->string('meta_title', 200)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('primary_keyword', 180)->nullable();
            $table->json('faq')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebooks');
    }
};
