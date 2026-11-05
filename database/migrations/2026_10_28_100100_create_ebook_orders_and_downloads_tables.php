<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 64)->unique();
            $table->foreignId('ebook_id')->constrained()->restrictOnDelete();
            $table->string('buyer_name', 160);
            $table->string('buyer_email', 254)->index();
            $table->string('buyer_phone', 40)->nullable();
            $table->unsignedInteger('amount_kobo');
            $table->char('currency', 3)->default('NGN');
            $table->string('status', 20)->default('pending')->index();
            $table->string('paystack_reference', 100)->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->string('fulfilled_pdf_path')->nullable();
            $table->string('referrer', 2048)->nullable();
            $table->string('utm_source', 255)->nullable();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('ebook_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('ebook_orders')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->unsignedInteger('token_generation')->default(1);
            $table->timestamp('expires_at')->index();
            $table->unsignedSmallInteger('max_downloads');
            $table->unsignedSmallInteger('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_downloads');
        Schema::dropIfExists('ebook_orders');
    }
};
