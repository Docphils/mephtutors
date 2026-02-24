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
        Schema::create('crms', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        // Institution details
        $table->string('institution_name');
        $table->text('institution_address');
        // Service
        $table->foreignId('service_item_id')->constrained()->cascadeOnDelete();

        $table->integer('number_of_tutors_required')->default(1);
        $table->integer('sessions_per_week')->default(1);

        $table->enum('delivery_mode', ['onsite', 'online', 'hybrid'])->default('onsite');

        $table->text('requirements')->nullable();

        $table->enum('engagement_type', [
            'short_term',
            'long_term',
            'contract',
            'club_management'
        ]);

        $table->enum('status', [
            'new',
            'contacted',
            'proposal_sent',
            'negotiating',
            'approved',
            'rejected',
            'deployed',
            'closed'
        ])->default('new');

        $table->timestamp('contacted_at')->nullable();
        $table->timestamp('closed_at')->nullable();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crms');
    }
};
