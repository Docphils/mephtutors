<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ebook_events')
            ->where('type', 'free_chapter_download')
            ->update(['type' => 'cheat_sheet_download']);
    }

    public function down(): void
    {
        DB::table('ebook_events')
            ->where('type', 'cheat_sheet_download')
            ->update(['type' => 'free_chapter_download']);
    }
};
