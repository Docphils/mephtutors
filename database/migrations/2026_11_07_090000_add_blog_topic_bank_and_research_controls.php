<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_topics', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('topic_type', 40)->index();
            $table->string('angle', 500);
            $table->foreignId('related_ebook_id')->nullable()->constrained('ebooks')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('blog_topic_suggestion_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_key', 100)->unique();
            $table->string('status', 20)->default('queued')->index();
            $table->string('topic_type', 40)->nullable();
            $table->unsignedSmallInteger('requested_count')->default(10);
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('error_message', 1000)->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('blog_tavily_monthly_usages', function (Blueprint $table) {
            $table->id();
            $table->string('month_key', 7)->unique();
            $table->unsignedInteger('credits_used')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::table('blog_generation_runs', function (Blueprint $table) {
            $table->foreignId('blog_topic_id')->nullable()->constrained('blog_topics')->nullOnDelete();
            $table->unsignedSmallInteger('word_target')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('blog_generation_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn('word_target');
            $table->dropConstrainedForeignId('blog_topic_id');
        });

        Schema::dropIfExists('blog_tavily_monthly_usages');
        Schema::dropIfExists('blog_topic_suggestion_batches');
        Schema::dropIfExists('blog_topics');
    }
};
