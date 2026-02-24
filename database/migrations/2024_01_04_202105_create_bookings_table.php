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
        Schema::create('bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('tutor_id');
            $table->unsignedBigInteger('tutor_request_id');
            $table->foreignId('service_item_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->json('days_times');
            $table->json('subjects')->nullable();
            $table->json('learners');
            $table->integer('sessions');
            $table->string('duration');
            $table->enum('tutorGender', ['Male', 'Female', 'Any']);
            $table->enum('curriculum', ['British', 'French', 'Nigerian', 'Blended', 'N/A'])->default('N/A');
            $table->enum('status', ['Pending', 'Adjust', 'Accepted', 'Active', 'Completed', 'Declined','Closed']);
            $table->string('classes');
            $table->integer('amount');
            $table->enum('paymentStatus', ['Pending', 'Confirmed', 'Paid'])->default('Pending');
            $table->string('paymentEvidence')->nullable();
            $table->string('payment_reference')->nullable()->unique();
            $table->enum('client_payment_status', ['Pending','Paid','Failed'])->default('Pending');
            $table->string('tutorRemarks')->nullable(); 
            $table->string('clientAcceptanceRemarks')->nullable();
            $table->string('clientApprovalRemarks')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        
            // Foreign key constraints linking to the users table
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('client_id')->references('id')->on('users');
            $table->foreign('tutor_id')->references('id')->on('users');
            $table->foreign('tutor_request_id')->references('id')->on('tutor_requests');

        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
