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
            $table->bigInteger('phone');
            $table->string('address');
            $table->date('DOB');
            $table->string('image');
            $table->enum('gender', ['Male', 'Female']);
            $table->enum('qualification', ['SSCE', 'Diploma', 'NCE', 'HND/BSc/BEd/BA/BEng', 'MSc/MA', 'PhD']);
            $table->string('discipline');
            $table->enum('experience', ['0-1 year', '2-5 years', '6-10 years', 'Above 10 years']);
            $table->string('CV');
            $table->enum('status', ['Pending', 'Approved', 'Review'])->default('Pending');
            $table->string('approvalRemarks');
            $table->text('careerProfile');
            $table->string('bankName');
            $table->string('accountName');
            $table->string('accountNumber');
            $table->text('approvalRemark')->nullable();
            $table->string('video')->nullable();
            $table->timestamps();

            // Foreign key constraint linking to the users table
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
