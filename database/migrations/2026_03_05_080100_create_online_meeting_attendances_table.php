<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('online_meeting_attendances')) {
            return;
        }

        Schema::create('online_meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['admin', 'client', 'tutor']);
            $table->enum('attendance_status', ['scheduled', 'attended', 'missed'])->default('scheduled');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['online_meeting_id', 'user_id']);
            $table->index(['user_id', 'attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_meeting_attendances');
    }
};
