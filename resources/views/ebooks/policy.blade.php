<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index,follow"><title>{{ $policy->title }} | MephEd</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900">
    @include('layouts.header')
    <main class="mx-auto max-w-3xl px-5 py-12">
        <h1 class="text-3xl font-black">{{ $policy->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">Effective {{ ($policy->published_at ?? $policy->updated_at)->toFormattedDateString() }}</p>
        <article class="prose prose-slate mt-8 max-w-none">
            {!! Illuminate\Support\Str::markdown($policy->content, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        </article>
    </main>
    @include('layouts.footer')
</body>
</html>
