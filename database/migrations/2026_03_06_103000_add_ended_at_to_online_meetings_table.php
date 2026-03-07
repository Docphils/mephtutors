<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_meetings', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        Schema::table('online_meetings', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
};
