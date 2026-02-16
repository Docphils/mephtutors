<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MephEd - Welcome</title>
     <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.bunny.net">
     <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <meta property="og:title" content="MephEd - Nigeria's best platform for matching exceptional tutors with learners" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.mephed.ng" />
    <meta property="og:site_name" content="MephEd" />
    <meta property="og:image" content="https://mephed.ng/images/banner.jpg" />
    <meta property="og:image:type" content="image/png">
    <meta property="og:description" content="Welcome to Nigeria's foremost tutor matching platform. We match desiring learners with exceptional tutors for all subjects and levels. We are also your best call for coding classes and extra-curricula clubs" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@mephed" />
    <meta name="twitter:title" content="MephEd - Nigeria's best platform for matching exceptional tutors with learners" />
    <meta name="twitter:description" content="Welcome to Nigeria's foremost tutor matching platform. We match desiring learners with exceptional tutors for all subjects and levels. We are also your best call for coding classes and extra-curricula clubs" />
    <meta name="twitter:image:src" content="https://mephed.ng/images/banner.jpg" />
    <meta property="twitter:image:type" content="image/png">
    <meta name="twitter:domain" content="https://www.mephed.ng" />


     <style>
        .text-container span {
            display: none;
        }
    </style>
      
      @vite(['resources/css/app.css', 'resources/js/app.js'])
      @livewireStyles
</head>

<body class="bg-cyan-300 font-sans leading-normal tracking-normal">
    <!-- Header Section -->
    @include('layouts.header')

    <!-- Welcome Banner -->
    <section class="my-4 text-center py-36 bg-cover bg-center rounded-sm shadow-md" style="background-image: url('/images/banner.png');">
        <h2 class="text-5xl font-bold text-white">Welcome to Meph<span class="text-cyan-300">Ed</span></h2>
        <div class="relative flex mx-auto items-baseline rounded-lg justify-center mt-5 text-2xl text-cyan-300 font-bold gap-1">Your one-stop solution for 
            <div class="text-container flex text-2xl bg-cyan-800 rounded-lg p-1 text-cyan-200">
                <span> Home Tutoring</span>
                <span> Coding</span>
                <span> Clubs</span>
            </div>
        </div>

            <div class="mt-5">
                <a wire:navigate class="inline-flex rounded-lg bg-pink-600 px-4 py-1.5 text-base font-semibold leading-7 text-white shadow-sm ring-1 ring-pink-600 hover:bg-pink-700 hover:ring-pink-700"
                    href="{{ route('client.tutorRequests.create') }}">
                    Request Tutor
                </a>
            </div>
    </section>

    <!-- Main Content Section -->

    <main class="container mx-auto py-10 px-6">
        

        <!-- Services Section -->
        <section class="py-20">
            <h3 class="text-3xl font-semibold text-gray-800 text-center">Our Services</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                    <img src="/images/home_tutoring.jpg" alt="Home Tutoring" class="w-full h-48 object-cover rounded-t-lg">
                    <h4 class="text-xl font-bold text-gray-800 mt-4">Home Tutoring</h4>
                    <p class="mt-4 text-gray-600">Personalized home tutoring for all subjects and levels.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                    <img src="/images/coding-classes.jpg" alt="Coding Classes" class="w-full h-48 object-cover rounded-t-lg">
                    <h4 class="text-xl font-bold text-gray-800 mt-4">Coding Classes</h4>
                    <p class="mt-4 text-gray-600">Learn coding from basic to advanced levels with our expert tutors.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                    <img src="/images/robotics.jpg" alt="Robotics" class="w-full h-48 object-cover rounded-t-lg">
                    <h4 class="text-xl font-bold text-gray-800 mt-4">School Clubs</h4>
                    <p class="mt-4 text-gray-600">Get expert club instructors for your school and other organizations. We offer coding, music, chess, etc.</p>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-20 bg-gradient-to-r from-cyan-300 to-cyan-400">
            <div class="container mx-auto text-center">
                <h3 class="text-3xl font-semibold text-gray-800">About Us</h3>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">MephEd is dedicated to providing top-notch home tutoring and innovative coding and robotics classes. Our experienced tutors and comprehensive curriculum ensure that every student achieves their full potential.</p>
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
                    <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Join our team of professional tutors reshaping the horizons of tutelage in our world. Our outstanding community of exceptional tutors and teams are ever ready to 
                        support you for maximum impact and efficiency. 
                    </p>
                    <div class="mt-4">
                        <a wire:navigate href="{{ route('register') }}" class="bg-blue-500 text-lg text-white px-4 py-2 rounded-lg hover:text-cyan-100 transition">Join Us</a>
                    </div>
                </div>
                <div class="relative sm:flex mx-auto gap-4 text-center justify-between">
                    <div class="relative">
                        <img src="images/teacher2.png" class="object-cover w-full h-48 border border-double border-amber-300 bg-white border-4 rounded-full" alt="">
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
                <livewire:contact-form lazy='on-load'>
            </div>
        </div>
    </section>
    <!-- Footer Section -->
    @include('layouts.footer')


    <!-- Scripts -->
    <script>
      const spans = document.querySelectorAll('.text-container span');
      let currentIndex = 0;

      function showNextSpan() {
          spans.forEach(span => span.style.display = 'none');
          spans[currentIndex].style.display = 'inline';
          currentIndex = (currentIndex + 1) % spans.length;
      }

      setInterval(showNextSpan, 3000); // Change text every 3 seconds
      showNextSpan(); // Show the first span initially
  </script>
   @livewireScripts
</body>
</html>
