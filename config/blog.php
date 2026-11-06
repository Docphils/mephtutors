<?php

return [
    'public_enabled' => (bool) env('BLOG_PUBLIC_ENABLED', true),
    'generation_enabled' => (bool) env('BLOG_GENERATION_ENABLED', false),
    'facebook_enabled' => (bool) env('BLOG_FACEBOOK_ENABLED', false),
    'facebook_page_name' => env('BLOG_FACEBOOK_PAGE_NAME', 'MephEd Educational Services'),
    'facebook_page_url' => env('BLOG_FACEBOOK_PAGE_URL', 'https://www.facebook.com/mephed'),
    'author_byline' => env('BLOG_AUTHOR_BYLINE', 'Author Name (MephEd)'),
    'timezone' => 'Africa/Lagos',
    'ai_provider' => env('BLOG_AI_PROVIDER', 'groq'),
    'source_domains' => [
        'education.gov.ng',
        'waecnigeria.org',
        'neco.gov.ng',
        'jamb.gov.ng',
        'nuc.edu.ng',
        'unesco.org',
        'unicef.org',
        'worldbank.org',
        'oecd.org',
        'cac.gov.ng',
        'trcn.gov.ng',
        'nrs.gov.ng',
        'napps.org.ng',
        'x.com',
        'www.nuc.edu.ng',
    ],
    'source_feed_urls' => [
        'https://education.gov.ng/feed/',
        'https://www.nuc.edu.ng/feed/',
    ],
];
