<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (! Schema::hasColumn('payments', 'programme_enquiry_assignment_id')) {
                $table->unsignedBigInteger('programme_enquiry_assignment_id')->nullable()->after('booking_id');
                $table->index('programme_enquiry_assignment_id', 'payments_programme_assignment_idx');
            }

            if (Schema::hasColumn('payments', 'booking_id')) {
                $table->dropForeign(['booking_id']);
            }
        });

        DB::statement('ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NULL');

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign('booking_id')->references('id')->on('bookings')->nullOnDelete();

            if (Schema::hasColumn('payments', 'programme_enquiry_assignment_id')) {
                $table->foreign('programme_enquiry_assignment_id', 'payments_programme_assignment_fk')
                    ->references('id')
                    ->on('programme_enquiry_assignments')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // Remove intervention-linked payments before restoring booking_id non-nullable.
        DB::table('payments')->whereNull('booking_id')->delete();

        Schema::table('payments', function (Blueprint $table): void {
            if (Schema::hasColumn('payments', 'programme_enquiry_assignment_id')) {
                $table->dropForeign('payments_programme_assignment_fk');
                $table->dropIndex('payments_programme_assignment_idx');
                $table->dropColumn('programme_enquiry_assignment_id');
            }

            $table->dropForeign(['booking_id']);
        });

        DB::statement('ALTER TABLE payments MODIFY booking_id BIGINT UNSIGNED NOT NULL');

        Schema::table('payments', function (Blueprint $table): void {
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }
};