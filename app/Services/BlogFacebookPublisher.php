<?php

namespace App\Services;

use App\Exceptions\FacebookRateLimitException;
use App\Models\BlogFacebookShare;
use App\Models\BlogPost;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BlogFacebookPublisher
{
    public function publish(int $shareId): void
    {
        $share = BlogFacebookShare::query()->with('post')->findOrFail($shareId);
        if ($share->status !== BlogFacebookShare::STATUS_QUEUED) {
            return;
        }

        if ($share->post === null
            || $share->post->status !== BlogPost::STATUS_PUBLISHED
            || $share->post->published_at === null) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_CANCELLED,
                'error_message' => 'The article is no longer published; Facebook delivery was cancelled.',
            ]);

            return;
        }

        if (! config('blog.facebook_enabled')) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_MANUAL,
                'error_message' => 'Automatic Facebook publishing was disabled before this queued post ran.',
            ]);

            return;
        }

        if (! config('blog.public_enabled')) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => 'The public blog is disabled; Facebook delivery was stopped.',
            ]);

            return;
        }

        $pageId = (string) config('services.facebook.page_id');
        $token = (string) config('services.facebook.page_access_token');
        $version = (string) config('services.facebook.graph_api_version');
        if (! ctype_digit($pageId) || $token === '' || preg_match('/^v\d+\.\d+$/', $version) !== 1) {
            $share->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => 'Facebook Page credentials or Graph API version are not configured correctly.',
            ]);

            return;
        }

        $postId = $share->blog_post_id;
        $claimed = DB::transaction(function () use ($shareId, $postId): ?BlogFacebookShare {
            $post = BlogPost::query()->lockForUpdate()->find($postId);
            $locked = BlogFacebookShare::query()->lockForUpdate()->findOrFail($shareId);
            if ($locked->status !== BlogFacebookShare::STATUS_QUEUED) {
                return null;
            }
            if ($post === null
                || $post->status !== BlogPost::STATUS_PUBLISHED
                || $post->published_at === null
                || $post->published_at->isFuture()) {
                $locked->update([
                    'status' => BlogFacebookShare::STATUS_CANCELLED,
                    'error_message' => 'The article is no longer publicly available; Facebook delivery was cancelled.',
                ]);

                return null;
            }
            $locked->update([
                'status' => BlogFacebookShare::STATUS_PUBLISHING,
                'attempt_count' => $locked->attempt_count + 1,
                'last_attempt_at' => now(),
                'error_message' => null,
            ]);

            $locked->setRelation('post', $post);

            return $locked->fresh()->setRelation('post', $post);
        });
        if ($claimed === null || $claimed->post === null) {
            return;
        }

        $post = $claimed->post;
        $link = route('blog.show', $post->slug);
        $excerpt = trim((string) $post->excerpt);
        if ($excerpt === '') {
            $excerpt = Str::limit(trim(strip_tags($post->body)), 220);
        }
        $message = Str::limit($post->title."\n\n".$excerpt."\n\nRead more from MephEd:", 1800, '');

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(10)
                ->timeout(30)
                ->post('https://graph.facebook.com/'.$version.'/'.$pageId.'/feed', [
                    'message' => $message,
                    'link' => $link,
                    'published' => true,
                ]);
        } catch (ConnectionException $exception) {
            $claimed->update([
                'status' => BlogFacebookShare::STATUS_UNCERTAIN,
                'error_message' => 'The connection ended before Facebook confirmed the result. Check the Page before trying again to avoid a duplicate post.',
            ]);
            Log::warning('Facebook did not confirm the blog post result.', ['share_id' => $claimed->id]);

            return;
        }

        if ($response->status() === 429) {
            $claimed->update([
                'status' => BlogFacebookShare::STATUS_QUEUED,
                'error_message' => 'Facebook rate-limited the request; it will be retried.',
            ]);
            throw new FacebookRateLimitException('Facebook rate-limited the publishing request.');
        }

        if ($response->serverError()) {
            $claimed->update([
                'status' => BlogFacebookShare::STATUS_UNCERTAIN,
                'error_message' => 'Facebook returned a server error. Check the Page before retrying to avoid a duplicate post.',
            ]);
            Log::warning('Facebook returned an ambiguous server error for the blog post.', [
                'share_id' => $claimed->id,
                'status' => $response->status(),
            ]);

            return;
        }

        if (! $response->successful()) {
            $providerMessage = $response->json('error.message');
            $message = is_string($providerMessage)
                ? mb_substr(trim((string) preg_replace('/\s+/', ' ', $providerMessage)), 0, 700)
                : 'Facebook rejected the publishing request.';
            $claimed->update([
                'status' => BlogFacebookShare::STATUS_FAILED,
                'error_message' => 'Facebook API error (HTTP '.$response->status().'): '.$message,
            ]);
            Log::warning('Facebook rejected the blog post.', [
                'share_id' => $claimed->id,
                'status' => $response->status(),
            ]);

            return;
        }

        $metaPostId = $response->json('id');
        if (! is_string($metaPostId) || preg_match('/^[A-Za-z0-9_]{1,100}$/', $metaPostId) !== 1) {
            $claimed->update([
                'status' => BlogFacebookShare::STATUS_UNCERTAIN,
                'error_message' => 'Facebook accepted the request without returning a verifiable post ID. Check the Page before retrying.',
            ]);
            Log::warning('Facebook returned an unexpected response for a blog post.', ['share_id' => $claimed->id]);

            return;
        }

        $claimed->update([
            'status' => BlogFacebookShare::STATUS_PUBLISHED,
            'meta_post_id' => $metaPostId,
            'published_at' => now(),
            'error_message' => null,
        ]);
    }
}
