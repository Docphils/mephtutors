<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function __invoke(): Response
    {
        abort_unless(config('blog.public_enabled'), 404);

        return response()->view('blog.feed', [
            'posts' => BlogPost::query()->published()->latest('published_at')->limit(30)->get(),
            'feedUrl' => route('blog.feed'),
        ], 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
