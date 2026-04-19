<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('online_meetings') || Schema::hasColumn('online_meetings', 'ended_at')) {
            return;
        }

        Schema::table('online_meetings', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('ends_at');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('online_meetings') || !Schema::hasColumn('online_meetings', 'ended_at')) {
            return;
        }

        Schema::table('online_meetings', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
};
