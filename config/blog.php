<?php

return [
    'public_enabled' => (bool) env('BLOG_PUBLIC_ENABLED', true),
    'generation_enabled' => (bool) env('BLOG_GENERATION_ENABLED', false),
    'facebook_enabled' => (bool) env('BLOG_FACEBOOK_ENABLED', false),
    'facebook_page_name' => env('BLOG_FACEBOOK_PAGE_NAME', 'MephEd Educational Services'),
    'facebook_page_url' => env('BLOG_FACEBOOK_PAGE_URL', 'https://www.facebook.com/mephed'),
    'author_byline' => env('BLOG_AUTHOR_BYLINE', 'MephEd Educational Services'),
    'timezone' => 'Africa/Lagos',
    'ai_provider' => env('BLOG_AI_PROVIDER', 'groq'),
    'default_content_word_target' => 700,
    'default_topic_batch_size' => 10,
    'default_topic_low_watermark' => 3,
    'default_tavily_monthly_credit_limit' => 1500,
    'default_tavily_max_results' => 10,
    'default_tavily_search_depth' => 'basic',
    'default_tavily_current_affairs_time_range' => 'month',
];
