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
        Schema::create('tutor_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('fullName');
            $table->string('phone');
            $table->string('state');
            $table->string('city');
            $table->string('address');
            $table->date('DOB');
            $table->enum('gender', ['Male', 'Female']);
            
            // CHANGE THESE TO NULLABLE
            $table->string('image')->nullable(); 
            $table->enum('qualification', ['SSCE', 'Diploma', 'NCE', 'HND/Bachelors', 'MSc/MA', 'PhD'])->nullable();
            $table->string('discipline')->nullable();
            $table->enum('experience', ['0-1 year', '2-5 years', '6-10 years', 'Above 10 years'])->nullable();
            $table->string('CV')->nullable();
            $table->text('careerProfile')->nullable();
            $table->string('bankName')->nullable();
            $table->string('accountName')->nullable();
            $table->string('accountNumber')->nullable();
            $table->string('approvalRemarks')->nullable(); // Also make this nullable
            
            $table->enum('status', ['Pending', 'Approved', 'Review'])->default('Pending');
            $table->text('approvalRemark')->nullable();
            $table->string('video')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles');
    }
};
