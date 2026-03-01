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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('phone')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->date('DOB')->nullable();
            $table->string('image')->nullable();
            $table->enum('gender', ['Male', 'Female']);
            $table->timestamps();

            // Foreign key constraint linking to the users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Adding an index to user_id
            $table->index('user_id');
        });
    }

public function down(): void
{
    Schema::dropIfExists('user_profiles');
}
};
