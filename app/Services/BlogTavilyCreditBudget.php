<?php

namespace App\Services;

use App\Models\BlogSetting;
use App\Models\BlogTavilyMonthlyUsage;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class BlogTavilyCreditBudget
{
    public function reserve(int $credits): void
    {
        if ($credits < 1) {
            throw new \InvalidArgumentException('At least one Tavily credit must be reserved.');
        }

        $month = now((string) config('blog.timezone', 'Africa/Lagos'))->format('Y-m');
        $limit = max(0, (int) BlogSetting::getValue('tavily_monthly_credit_limit', 1500));

        DB::transaction(function () use ($credits, $limit, $month): void {
            $timestamp = now();
            DB::table('blog_tavily_monthly_usages')->insertOrIgnore([
                'month_key' => $month,
                'credits_used' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            $usage = BlogTavilyMonthlyUsage::query()
                ->where('month_key', $month)
                ->lockForUpdate()
                ->firstOrFail();

            if ($usage->credits_used + $credits > $limit) {
                throw new RuntimeException(sprintf(
                    'The monthly Tavily credit limit has been reached (%d of %d credits used). Increase the limit in Blog Settings or wait for the monthly reset.',
                    $usage->credits_used,
                    $limit,
                ));
            }

            $usage->update([
                'credits_used' => $usage->credits_used + $credits,
                'last_used_at' => $timestamp,
            ]);
        });
    }

    public function currentMonthUsage(): int
    {
        return (int) BlogTavilyMonthlyUsage::query()
            ->where('month_key', now((string) config('blog.timezone', 'Africa/Lagos'))->format('Y-m'))
            ->value('credits_used');
    }
}
