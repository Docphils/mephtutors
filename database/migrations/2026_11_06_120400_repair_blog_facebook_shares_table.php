<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blog_facebook_shares')) {
            return;
        }

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
        // Keep the table intact because it may have been created by an earlier migration.
    }
};
