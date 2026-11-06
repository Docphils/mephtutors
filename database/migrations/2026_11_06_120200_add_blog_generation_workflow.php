<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('hero_image_path')->nullable();
        });

        Schema::create('blog_generation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run_key', 100)->unique();
            $table->date('scheduled_for')->nullable()->index();
            $table->string('status', 20)->default('queued')->index();
            $table->foreignId('blog_post_id')->nullable()->unique()->constrained('blog_posts')->nullOnDelete();
            $table->string('provider', 30)->nullable();
            $table->json('metadata')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->string('notification_status', 20)->nullable();
            $table->string('notification_error', 1000)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_facebook_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->unique()->constrained('blog_posts')->cascadeOnDelete();
            $table->string('status', 20)->default('manual')->index();
            $table->string('meta_post_id', 100)->nullable()->unique();
            $table->string('error_message', 1000)->nullable();
            $table->unsignedSmallInteger('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_facebook_shares');
        Schema::dropIfExists('blog_generation_runs');

        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('hero_image_path');
        });
    }
};
