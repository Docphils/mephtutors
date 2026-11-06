<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DEFAULT_FEEDS = [
        'https://education.gov.ng/feed/',
        'https://www.nuc.edu.ng/feed/',
    ];

    public function up(): void
    {
        $feedSetting = DB::table('blog_settings')->where('key', 'source_feed_urls')->first();
        $feeds = $feedSetting ? json_decode((string) $feedSetting->value, true) : null;

        if (! is_array($feeds) || $feeds === []) {
            DB::table('blog_settings')->updateOrInsert(
                ['key' => 'source_feed_urls'],
                ['value' => json_encode(self::DEFAULT_FEEDS, JSON_THROW_ON_ERROR), 'updated_at' => now(), 'created_at' => $feedSetting?->created_at ?? now()],
            );

            $domainSetting = DB::table('blog_settings')->where('key', 'source_domains')->first();
            $domains = $domainSetting ? json_decode((string) $domainSetting->value, true) : null;
            if (is_array($domains)) {
                $domains = array_values(array_filter($domains, 'is_string'));
                $domains[] = 'www.nuc.edu.ng';
                $domains = array_values(array_unique($domains));

                DB::table('blog_settings')->where('key', 'source_domains')->update([
                    'value' => json_encode($domains, JSON_THROW_ON_ERROR),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Preserve administrator-managed research settings on rollback.
    }
};
