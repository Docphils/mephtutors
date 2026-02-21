<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MephEd') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="min-h-screen bg-cyan-50 font-sans antialiased text-slate-900">

    <div x-data="{ sidebarOpen: false, showEditor: false }" @edit-user-profile.window="showEditor = true"
        x-on:close-profile-editor.window="showEditor = false" class="flex min-h-screen w-full overflow-x-hidden">

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

        <!-- ===== MAIN CONTENT ===== -->
        <div class="relative w-full flex-1 flex flex-col min-h-screen">

            <!-- Top Bar -->
            <header class="bg-white shadow-sm border-b">
                @canany(['Tutor', 'Client'])
                    @include('layouts.header')
                @endcanany
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

            <div x-show="showEditor">
                <livewire:partials.user-profile-editor />
            </div>

            <!-- Page Content -->
            <main class="relative w-full flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

        </div>

    </div>

    @livewireScripts
</body>

</html>
