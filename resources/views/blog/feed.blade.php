{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>MephEd Blog</title>
        <link>{{ route('blog.index') }}</link>
        <description>Practical ideas and insights from MephEd.</description>
        <language>en</language>
        <atom:link xmlns:atom="http://www.w3.org/2005/Atom" href="{{ $feedUrl }}" rel="self" type="application/rss+xml"/>
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('blog.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('blog.show', $post->slug) }}</guid>
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
                <description>{{ $post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->body), 240) }}</description>
                <dc:creator>MephEd Editorial Team</dc:creator>
            </item>
        @endforeach
    </channel>
</rss>
