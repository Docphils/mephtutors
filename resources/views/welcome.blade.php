<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MephEd - Get Tutors Online</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

    <meta property="og:title"
        content="MephEd - Nigeria's best platform for matching exceptional tutors with learners" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.mephed.ng" />
    <meta property="og:site_name" content="MephEd" />
    <meta property="og:image" content="https://mephed.ng/images/banner.jpg" />
    <meta property="og:image:type" content="image/png">
    <meta property="og:description"
        content="Welcome to Nigeria's foremost tutor matching platform. We match desiring learners with exceptional tutors for all subjects and levels. We are also your best call for coding classes and extra-curricula clubs" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@mephed" />
    <meta name="twitter:title"
        content="MephEd - Nigeria's best platform for matching exceptional tutors with learners" />
    <meta name="twitter:description"
        content="Welcome to Nigeria's foremost tutor matching platform. We match desiring learners with exceptional tutors for all subjects and levels. We are also your best call for coding classes and extra-curricula clubs" />
    <meta name="twitter:image:src" content="https://mephed.ng/images/banner.jpg" />
    <meta property="twitter:image:type" content="image/png">
    <meta name="twitter:domain" content="https://www.mephed.ng" />


    <!-- Alpine.js for interactivity (used for carousel) -->
    <script defer src="https://unpkg.com/alpinejs@3.16.1/dist/cdn.min.js"></script>

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

<body class="bg-cyan-300 font-sans leading-normal tracking-normal h-screen">
    <!-- Header Section -->
    @include('layouts.header')

    <!-- Hero Carousel -->
    <livewire:hero-carousel />


    <!-- Main Content Section -->

    <main class="container mx-auto py-10 px-6">


        <!-- Dynamic services rendered by Livewire -->
        <div>
            <livewire:welcome-services />
        </div>

        <!-- How It Works / Details -->
        <section class="py-12">
            <div class="max-w-5xl mx-auto px-4">
                <h3 class="text-3xl font-semibold text-gray-800 text-center">How It Works</h3>
                <p class="text-center text-gray-600 mt-2">Simple steps to find great tutors and learning programs.</p>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <div class="text-3xl">🔎</div>
                        <h4 class="mt-4 font-bold">Browse Programs</h4>
                        <p class="mt-2 text-gray-600">Explore subjects, tutors and formats that suit your needs.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <div class="text-3xl">📅</div>
                        <h4 class="mt-4 font-bold">Book A Lesson</h4>
                        <p class="mt-2 text-gray-600">Schedule flexible sessions, in-person or online.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <div class="text-3xl">👩‍🏫</div>
                        <h4 class="mt-4 font-bold">Learn With Experts</h4>
                        <p class="mt-2 text-gray-600">Work with vetted tutors and track your progress.</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 text-center">
                        <div class="text-3xl">🔒</div>
                        <h4 class="mt-4 font-bold">Safe & Secure</h4>
                        <p class="mt-2 text-gray-600">Secure payments, clear policies and responsive support.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="py-12 bg-gray-50">
            <div class="max-w-4xl mx-auto px-4">
                <h3 class="text-3xl font-semibold text-gray-800 text-center">Frequently Asked Questions</h3>
                <p class="text-center text-gray-600 mt-2">Common questions about our services and how to get started.
                </p>

                <div x-data="{ open: null }" class="mt-8 space-y-3">
                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 1 ? open = null : open = 1"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">How do I book a tutor?</span>
                            <span x-show="open !== 1">+</span>
                            <span x-show="open === 1">−</span>
                        </button>
                        <div x-show="open === 1" x-collapse class="px-6 pb-4 text-gray-600">Browse programs or
                            request a tutor via our booking form. Choose schedule and lesson format, then confirm
                            payment.</div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 2 ? open = null : open = 2"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">Do you offer online lessons?</span>
                            <span x-show="open !== 2">+</span>
                            <span x-show="open === 2">−</span>
                        </button>
                        <div x-show="open === 2" x-collapse class="px-6 pb-4 text-gray-600">Yes - many tutors teach
                            remotely using Zoom, Google Meet or other platforms. Choose the online option when
                            booking.</div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 3 ? open = null : open = 3"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">What subjects do you cover?</span>
                            <span x-show="open !== 3">+</span>
                            <span x-show="open === 3">−</span>
                        </button>
                        <div x-show="open === 3" x-collapse class="px-6 pb-4 text-gray-600">We cover school
                            subjects, coding, design, music, languages and school clubs. If you need something else,
                            contact support.</div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 4 ? open = null : open = 4"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">Can schools partner with MephEd?</span>
                            <span x-show="open !== 4">+</span>
                            <span x-show="open === 4">−</span>
                        </button>
                        <div x-show="open === 4" x-collapse class="px-6 pb-4 text-gray-600">Yes - we provide club
                            instructors, EdTech strategy and bespoke programs. Use the Partner With Us link to get
                            started.</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-20 bg-gradient-to-r from-cyan-300 to-cyan-400">
            <div class="container mx-auto text-center">
                <h3 class="text-3xl font-semibold text-gray-800">About Us</h3>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">MephEd is dedicated to providing top-notch home
                    tutoring and innovative coding and robotics classes. Our experienced tutors and comprehensive
                    curriculum ensure that every student achieves their full potential.</p>
            </div>
        </section>

    </main>


    <!-- Testimonials Section -->
    <livewire:testimonials.home-testimonials />

    <!-- Become a Tutor Section -->
    <section class="px-8 sm:px-20 py-20 bg-gradient-to-r  from-cyan-400 to-cyan-300">
        <div class="container sm:flex justify-between items-center mx-auto text-center gap-4">
            <div class="text-justify my-4">
                <h3 class="text-3xl font-semibold text-gray-50">Become a Tutor</h3>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Join our team of professional tutors reshaping
                    the horizons of tutelage in our world. Our outstanding community of exceptional tutors and teams are
                    ever ready to
                    support you for maximum impact and efficiency.
                </p>
                <div class="mt-4">
                    <a wire:navigate href="{{ route('register') }}"
                        class="bg-blue-500 text-lg text-white px-4 py-2 rounded-lg hover:text-cyan-100 transition">Join
                        Us</a>
                </div>
            </div>
            <div class="relative sm:flex mx-auto gap-4 text-center justify-between">
                <div class="relative">
                    <img src="images/teacher2.png"
                        class="object-cover w-full h-48 border border-double border-amber-300 bg-white border-4 rounded-full"
                        alt="">
                </div>
                <div class="flex items-start border-2 h-48 bg-amber-300 rounded-full">
                    <img src="images/teacher.png" class="object-cover w-full h-64 border-b" alt="">
                </div>
            </div>
        </div>
    </section>
    <!-- Contact Form Section -->
    <section class="py-20 bg-gradient-to-r from-cyan-100 to-cyan-200">
        <div class="container mx-auto">
            <h3 class="text-3xl font-semibold text-gray-800 text-center">Contact Us</h3>
            <div class="mt-12 max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg">
                <livewire:contact-form lazy="on-load" />
            </div>
        </div>
    </section>
    <!-- Footer Section -->
    @include('layouts.footer')
    <!-- Scripts handled by Alpine.js (carousel & interactions) -->
    @livewireScripts
</body>

</html>
