<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0e7490">
    <meta name="application-name" content="{{ config('app.name', 'MephEd') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'MephEd') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('pwa/icons/apple-touch-icon.png') }}">

    <title>{{ $title ?? 'MephEd' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'MephEd educational services.' }}">
    <meta name="robots" content="{{ $metaRobots ?? 'index,follow' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    <link rel="alternate" type="text/plain" title="LLMs" href="{{ route('llms') }}">

    <meta property="og:title" content="{{ $title ?? 'MephEd' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'MephEd educational services.' }}">
    <meta property="og:type" content="{{ $ogType ?? 'website' }}">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta property="og:image" content="{{ $ogImage ?? asset('images/MephEd.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'MephEd' }}">
    <meta name="twitter:description" content="{{ $metaDescription ?? 'MephEd educational services.' }}">
    <meta name="twitter:image" content="{{ $ogImage ?? asset('images/MephEd.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17506686809"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'AW-17506686809');
    </script>
</head>

<body class="font-sans text-gray-900 antialiased h-screen">
    @if (!empty($structuredData))
        <script type="application/ld+json">{!! $structuredData !!}</script>
    @endif
    <div class="min-h-full bg-slate-50 text-slate-800">
        @include('layouts.header')



        <!-- Page Content -->
        <main class="w-full mx-auto">
            {{ $slot }}
        </main>
        @include('layouts.footer')
    </div>
    <x-pwa-install-button class="fixed bottom-4 right-4 z-[70] sm:hidden" />
    @livewireScripts
</body>

</html>
