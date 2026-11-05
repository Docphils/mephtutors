<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->longText('description')->nullable()->after('subtitle');
            $table->longText('audience')->nullable()->after('description');
            $table->longText('contents')->nullable()->after('audience');
        });
    }

    public function down(): void
    {
        Schema::table('ebooks', function (Blueprint $table) {
            $table->dropColumn(['description', 'audience', 'contents']);
        });
    }
};
