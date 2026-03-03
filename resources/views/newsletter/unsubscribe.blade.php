<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $success ? 'Unsubscribed' : 'Unsubscribe Link Error' }} | MephEd</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-slate-50 text-slate-800 antialiased min-h-screen">
    @include('layouts.header')

    <main class="max-w-2xl mx-auto px-4 py-16">
        <section class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $success ? 'bg-cyan-100 text-cyan-700' : 'bg-rose-100 text-rose-700' }}">
                    <i class="fa-solid {{ $success ? 'fa-envelope-circle-check' : 'fa-circle-exclamation' }} text-xl"></i>
                </div>
                <div class="flex-1">
                    <p class="text-xs uppercase tracking-[0.18em] font-black {{ $success ? 'text-cyan-600' : 'text-rose-600' }}">
                        Newsletter Preference
                    </p>
                    <h1 class="text-3xl font-black mt-1 text-slate-900">
                        {{ $success ? 'You Are Unsubscribed' : 'Unable To Unsubscribe' }}
                    </h1>
                    <p class="mt-4 text-slate-600 leading-relaxed">{{ $message }}</p>
                </div>
            </div>

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('welcome') }}"
                    class="px-5 py-3 rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition-all">
                    Back to Home
                </a>
                <a href="{{ route('contact') }}"
                    class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 font-bold hover:bg-slate-100 transition-all">
                    Contact Support
                </a>
            </div>
        </section>
    </main>

    @include('layouts.footer')
</body>
</html>
