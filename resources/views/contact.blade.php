<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact MephEd - Nigeria's Best Tutoring Company</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />

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

<body class="bg-cyan-800 font-sans leading-normal tracking-normal h-screen">
    <!-- Header Section -->
    @include('layouts.header')
    @if (session()->has('info'))
        <div class="flex justify-center mx-auto bg-cyan-100 border-t-4 border-cyan-500 rounded-b text-cyan-900 px-4 py-3 shadow-md mb-6"
            role="alert">
            <div class="flex py-1 items-center">
                <i class="fa-solid fa-circle-info text-cyan-900 text-3xl mr-2"></i>
                <div>
                    <p class="font-bold">Info</p>
                    <p class="text-sm">{{ session('info') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Contact Form Section -->
    <section class="py-20 bg-gradient-to-r from-cyan-800 to-cyan-900">
        <div class="container mx-auto w-3/4 md:w-2/3">
            <h3 class="text-3xl font-semibold text-gray-100 text-center">Contact Us</h3>
            <h6 class="text-xl my-4 text-gray-50 text-center">Send us a message and a member of our team will reach out
                to you within the shortest possible time. </h6>
            <div class="relative grid mx-auto sm:grid-cols-3 gap-8">
                <div class="relative mt-12 sm:col-span-2 w-full mx-auto bg-white p-8 rounded-lg shadow-lg">
                    <livewire:contact-form>
                </div>

                <div class="mt-12 md:ml-6 border-l-8 p-6 shadow-lg shadow-cyan-300 w-full rounded-lg text-cyan-300">
                    <div class="mb-10">
                        <h4 class="text-white text-xl font-bold">Contact Address:</h4>
                        <p> House 101, Zone D, Apo Resettlement, Abuja, Nigeria</p>
                    </div>
                    <div class="relative mb-10">
                        <h4 class="text-white text-xl font-bold">Contact Info:</h4>
                        <div class="flex flex-wrap md:flex-row sm:text-sm justify-between text-between">
                            <p>07062599737</p>
                            <p>08062869170</p>
                            <p class="mt-2 sm:mt-0">Email: info@mephed<wbr>.ng</p>
                        </div>
                    </div>

                    <div class="mb-10">
                        <h4 class="text-white text-xl font-bold">Office Hours:</h4>
                        <p> Mondays - Fridays: 8:00AM to 6:00PM (WAT)</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    @include('layouts.footer')
    @livewireScripts
</body>

</html>
