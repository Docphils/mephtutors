<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programme_enquiry_assignments', function (Blueprint $table): void {
            if (! Schema::hasColumn('programme_enquiry_assignments', 'start_date')) {
                $table->date('start_date')->nullable()->after('tutor_notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('programme_enquiry_assignments', function (Blueprint $table): void {
            if (Schema::hasColumn('programme_enquiry_assignments', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};