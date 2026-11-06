<?php

namespace App\Services;

use App\Jobs\PublishBlogPostToFacebook;
use App\Models\BlogFacebookShare;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class BlogFacebookWorkflow
{
    public function queueAfterPublication(BlogPost $post): BlogFacebookShare
    {
        if ($post->status !== BlogPost::STATUS_PUBLISHED || $post->published_at === null || $post->published_at->isFuture()) {
            throw new RuntimeException('Only a published article can be shared to Facebook.');
        }

        $enabled = (bool) config('blog.facebook_enabled');
        $share = BlogFacebookShare::query()->firstOrCreate(
            ['blog_post_id' => $post->id],
            ['status' => $enabled ? BlogFacebookShare::STATUS_QUEUED : BlogFacebookShare::STATUS_MANUAL],
        );

        if (! $enabled) {
            if (in_array($share->status, [
                BlogFacebookShare::STATUS_QUEUED,
                BlogFacebookShare::STATUS_FAILED,
                BlogFacebookShare::STATUS_CANCELLED,
            ], true)) {
                $share->update([
                    'status' => BlogFacebookShare::STATUS_MANUAL,
                    'error_message' => 'Automatic Facebook publishing is disabled. Use the prepared manual-share link.',
                ]);
            }

            return $share->fresh();
        }

        if (! config('blog.public_enabled')) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => 'The public blog is disabled, so the article URL cannot be shared.',
            ]);

            return $share->fresh();
        }

        if (! $this->credentialsConfigured()) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => 'Facebook API publishing is enabled, but the Page ID or Page access token is not configured.',
            ]);
            Log::warning('Facebook publishing is enabled without complete Page credentials.', ['post_id' => $post->id]);

            return $share->fresh();
        }

        if (in_array($share->status, [
            BlogFacebookShare::STATUS_PUBLISHED,
            BlogFacebookShare::STATUS_PUBLISHING,
            BlogFacebookShare::STATUS_UNCERTAIN,
        ], true)) {
            if ($share->status === BlogFacebookShare::STATUS_PUBLISHING
                && $share->last_attempt_at?->lt(now()->subMinutes(30))) {
                $share->update([
                    'status' => BlogFacebookShare::STATUS_UNCERTAIN,
                    'error_message' => 'The previous attempt did not finish. Check the Page before retrying to avoid duplicate posts.',
                ]);
            }

            return $share->fresh();
        }

        $share->update([
            'status' => BlogFacebookShare::STATUS_QUEUED,
            'error_message' => null,
        ]);
        PublishBlogPostToFacebook::dispatch($share->id)->onConnection('blog')->onQueue('blog');

        return $share->fresh();
    }

    private function credentialsConfigured(): bool
    {
        $pageId = (string) config('services.facebook.page_id');
        $token = (string) config('services.facebook.page_access_token');
        $version = (string) config('services.facebook.graph_api_version');

        return ctype_digit($pageId)
            && $token !== ''
            && preg_match('/^v\d+\.\d+$/', $version) === 1;
    }
}
