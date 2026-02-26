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
        //
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('title');
            $table->text('body');
            $table->text('body2')->nullable();
            $table->enum('recipients', ['All', 'Admins', 'Clients', 'Tutors', 'TestTutor', 'TutorsWithProfile', 'TutorsWithoutProfile']);
            $table->enum('status', ['Draft', 'Sent'])->default('Draft');
            $table->json('sent_to')->nullable();
            $table->string('attachments')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
