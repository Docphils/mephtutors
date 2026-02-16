<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MephEd - Our Services</title>
     <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.bunny.net">
     <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
     <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17506686809">
        </script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'AW-17506686809');
        </script>
</head>
<body class="bg-cyan-300 font-sans leading-normal tracking-normal">
   @include('layouts.header')
    <!-- Services Section -->
    <div class="relative overflow-hidden bg-cyan-800 pt-16 pb-32 space-y-24">
        <div class="">
            <h3 class="text-3xl font-semibold text-gray-100 text-center">Our Services</h3>
            <p class="text-lg text-gray-50 text-center max-w-2xl mx-auto mt-4">At MephEd, we offer a variety of services to help you excel in your education and personal development. Explore our offerings below.</p>
        </div>
        <div class="relative">
            <div class="lg:mx-auto lg:grid lg:max-w-7xl lg:grid-flow-col-dense lg:grid-cols-2 lg:gap-24 lg:px-8 ">
                <div class="mx-auto max-w-xl px-6 lg:mx-0 lg:max-w-none lg:py-16 lg:px-0 ">
    
                    <div>
                        <div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-8 w-8 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.75L12 3l9 6.75v7.5a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 17.25v-7.5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 21V9.75h6V21" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15h6" />
                                  </svg>                                  
                            </span>
                        </div>
    
                        <div class="mt-6">
                            <h2 class="text-3xl font-bold tracking-tight text-white">
                                Home Tutoring:
                            </h2>
                            <p class="mt-4 text-lg text-gray-300">
                                Experience personalized home tutoring for every subject and level. Enjoy customized lessons designed just for you to enhance your learning and achieve your academic and career goals.
                            </p>
                            <div class="mt-6">
                                <a wire:navigate class="inline-flex rounded-lg bg-pink-600 px-4 py-1.5 text-base font-semibold leading-7 text-white shadow-sm ring-1 ring-pink-600 hover:bg-pink-700 hover:ring-pink-700"
                                    href="{{ route('client.tutorRequests.create') }}" >
                                    Request Tutor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-12 sm:mt-16 lg:mt-0">
                    <div class="-mr-48 pl-6 md:-mr-16 lg:relative lg:m-0 lg:h-full lg:px-0">
                        <img loading="lazy" width="647" height="486"
                            class="w-full rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 lg:absolute lg:left-0 lg:h-full lg:w-auto lg:max-w-none"
                            style="color:transparent" src="/images/home_tutoring.jpg">
                    </div>
                </div>
            </div>
        </div>
    
    
    
        <div class="relative">
            <div class="lg:mx-auto lg:grid lg:max-w-7xl lg:grid-flow-col-dense lg:grid-cols-2 lg:gap-24 lg:px-8 ">
                <div class="mx-auto max-w-xl px-6 lg:mx-0 lg:max-w-none lg:py-16 lg:px-0 lg:col-start-2">
                    <div>
                        <div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-8 w-8 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18a2 2 0 012 2v14a2 2 0 01-2 2H3a2 2 0 01-2-2V5a2 2 0 012-2z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12l-2 2m0-4l2 2m8-2l-2 2m0-4l2 2M6 8h12M6 16h12" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-6">
                            <h2 class="text-3xl font-bold tracking-tight text-white">
                                Coding Classes:
                            </h2>
                            <p class="mt-4 text-lg text-gray-300">
                                Learn coding from basics to advanced levels with our expert tutors. Whether you're starting out or looking to master complex concepts, our personalized guidance will help you succeed.
                            </p>
                            <div class="mt-6">
                                <a wire:navigate class="inline-flex rounded-lg bg-pink-600 px-4 py-1.5 text-base font-semibold leading-7 text-white shadow-sm ring-1 ring-pink-600 hover:bg-pink-700 hover:ring-pink-700"
                                    href="{{ route('codingRequest.index') }}">
                                    Book now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-12 sm:mt-16 lg:mt-0">
                    <div class="-ml-48 pr-6 md:-ml-16 lg:relative lg:m-0 lg:h-full lg:px-0">
                        <img alt="Inbox user interface" loading="lazy" width="647" height="486"
                            class="w-full rounded-xl shadow-xl ring-1 ring-black ring-opacity-5 lg:absolute lg:right-0 lg:h-full lg:w-auto lg:max-w-none"
                            style="color:transparent" src="/images/coding-classes.jpg">
                    </div>
                </div>
            </div>
        </div>
    
    
    
        <div class="relative">
            <div class="lg:mx-auto lg:grid lg:max-w-7xl lg:grid-flow-col-dense lg:grid-cols-2 lg:gap-24 lg:px-8 ">
                <div class="mx-auto max-w-xl px-6 lg:mx-0 lg:max-w-none lg:py-16 lg:px-0 ">
                    <div>
                        <div>
                            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-pink-500">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-8 w-8 text-white">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 2.25v3M7.5 4.5v1.5m9-1.5v1.5M3.75 12.75h16.5m-1.5-2.25a5.25 5.25 0 01-5.25 5.25 5.25 5.25 0 01-5.25-5.25m5.25 5.25v4.5m-2.25-4.5h4.5m-7.5 0h1.5m-1.5 0v4.5m9-4.5h-1.5m1.5 0v4.5M8.25 9.75h1.5M8.25 11.25h1.5M14.25 9.75h1.5M14.25 11.25h1.5M12 9.75h.008v.008H12V9.75z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-6">
                            <h2 class="text-3xl font-bold tracking-tight text-white">
                                Co-curricular Clubs:
                            </h2>
                            <p class="mt-4 text-lg text-gray-300">
                                Whether you want to render an impactful community service for shaping the future, or you want your school to stand out through co-curricular activities, MephEd is here for you.
                            </p>
                            <div class="mt-6">
                                <a wire:navigate class="inline-flex rounded-lg bg-pink-600 px-4 py-1.5 text-base font-semibold leading-7 text-white shadow-sm ring-1 ring-pink-600 hover:bg-pink-700 hover:ring-pink-700"
                                    href="{{ route('clubRequest.index') }}">
                                    Book now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-12 sm:mt-16 lg:mt-0">
                    <div class="-mr-48 pl-6 md:-mr-16 lg:relative lg:m-0 lg:h-full lg:px-0">
                        <img loading="lazy" width="646" height="485"
                            class="w-full rounded-xl shadow-2xl ring-1 ring-black ring-opacity-5 lg:absolute lg:left-0 lg:h-full lg:w-auto lg:max-w-none"
                            style="color:transparent"
                            src="/images/robotics.jpg">
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footer')
    @livewireScripts
</body>
</html>
