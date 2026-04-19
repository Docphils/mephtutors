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

    <title>{{ config('app.name', 'MephEd') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script type="text/javascript" src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-cyan-50 font-sans antialiased text-slate-900">

    <div x-data="{ sidebarOpen: false, showEditor: false }" @edit-user-profile.window="showEditor = true"
        x-on:close-profile-editor.window="showEditor = false" class="flex h-screen w-full overflow-hidden">

        <!-- ===== MOBILE OVERLAY ===== -->
        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-40 sm:hidden" x-cloak></div>

        <!-- ===== SIDEBAR ===== -->
        @can('Client')
            @include('layouts.client-sidebar')
        @endcan
        @can('Admin')
            @include('layouts.admin-sidebar')
        @endcan
        @can('Tutor')
            @include('layouts.tutor-sidebar', ['tutorProfile' => $tutorProfile])
        @endcan

        <!-- ===== MAIN CONTENT ===== -->
        <div class="relative min-w-0 flex-1 flex flex-col min-h-screen">

            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b">
                <div class="relative flex items-center justify-between px-4 sm:px-6 py-4 gap-2">

                    <!-- Mobile Toggle -->
                    <button @click="sidebarOpen = true" class="sm:hidden text-slate-700 text-xl">
                        <i class="fas fa-bars"></i>
                    </button>

                    <!-- Header Slot -->
                    <div class="relative w-full text-lg font-semibold text-slate-800">
                        {{ $header ?? 'Dashboard' }}
                    </div>

                </div>
            </header>

            <!-- Page Content -->
            <main class="relative w-full flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>

    </div>
    <x-pwa-install-button class="fixed bottom-4 right-4 z-[70] sm:hidden" />

    @livewireScripts
</body>

</html>
