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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->decimal('amount');
            $table->string('evidence')->nullable();
            $table->enum('status', ['Pending','Earned', 'Paid']);
            $table->json('dispute')->nullable();
            $table->unsignedBigInteger('tutor_id');
            $table->timestamps();


            // Foreign key constraints linking to the users table
            $table->foreign('tutor_id')->references('id')->on('users');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
