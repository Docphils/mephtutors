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

        .carousel-button {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0, 0, 0, 0.4);
        color: white;
        padding: 8px 12px;
        border-radius: 50%;
        font-size: 24px;
        cursor: pointer;
        border: none;
        z-index: 100;
    }
    .carousel-button:hover {
        background: rgba(0, 0, 0, 0.6);
    }
    </style>
      
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
    <!-- Header Section -->
    @include('layouts.header')

    <!-- Carousel Banner -->
    <section class="mb-4 text-center bg-cover bg-center rounded-sm shadow-md relative overflow-hidden" style="height: 550px;">
        <div id="carousel" class="carousel-slides h-full w-full relative">
            <!-- Slide 1 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100 z-10" style="background-image: url('/images/banner.jpg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50 space-y-2">
                    <h2 class="text-5xl font-bold">Welcome to Meph<span class="text-cyan-300">Ed</span></h2>
                    <p class="mt-4 text-2xl text-cyan-200">Your one-stop solution for personalized;</p>
                        <ul class="space-y-2 mb-4">
                            <li class="text-xl text-cyan-300">- Home Tutoring</li>
                            <li class="text-xl text-cyan-300">- Coding and IT Instructions</li>
                            <li class="text-xl text-cyan-300">- Club Management and EdTech Services</li>
                        </ul>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Learn More
                    </a>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100 z-10" style="background-image: url('/images/b-home-tutoring.jpg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50 space-y-2">
                    <h2 class="text-4xl font-bold text-cyan-300">Home Lessons That Deliver Results</h2>
                    <p class="mt-4 text-2xl ">Unlock personalized academic support from the comfort of your home.</p>
                        <ul class="space-y-2 mb-4">
                            <li class="text-xl text-cyan-200">Math, Sciences, English & more — tailored to your child’s needs.</li>
                            
                        </ul>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Book a Home Tutor
                    </a>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/coding-banner2.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"> Learn to Code;  <span class="text-cyan-300">Beginner to Pro</span></h2>
                    <p class="mt-4 text-2xl text-cyan-100">Master modern coding stacks with expert guidance.</p>
                    <p class="mt-4 text-xl text-cyan-200">Frontend, Backend, Fullstack (HTML, CSS, JavaScript, React, PHP, Laravel, MERN).</p>
                    <a href="https://forms.gle/omFaQV4wnWaCwTQz6"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Start Your Coding Journey
                    </a>
                </div>
            </div>
            <!-- Slide 4 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/b-waec.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"> Be Exam-Ready.   <span class="text-cyan-300">Always.</span></h2>
                    <p class="mt-4 text-2xl text-cyan-100">WAEC, NECO, JAMB, BECE, IELTS - we’ve got you covered.</p>
                    <p class="mt-4 text-xl text-cyan-200">📘 Structured revision plans. Smart practice strategies.</p>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Enroll for Exam Prep
                    </a>
                </div>
            </div>
            <!-- Slide 5 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/coding-banner2.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"><span class="text-cyan-300">Private Coding Lessons at </span>Your Pace</h2>
                    <p class="mt-4 text-2xl text-cyan-100">One-on-one or group classes, online or at home.</p>
                    <p class="mt-4 text-xl text-cyan-200">Ideal for kids, teens, and professionals.</p>
                    <a  href="https://forms.gle/omFaQV4wnWaCwTQz6"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Request a Coding Instructor
                    </a>
                </div>
            </div>
            <!-- Slide 6 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/b-home-lessons.jpg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"><span class="text-cyan-300">Struggling with Math or Science?</span></h2>
                    <p class="mt-4 text-2xl text-cyan-100">Targeted improvement classes for key subjects.</p>
                    <p class="mt-4 text-xl text-cyan-200">📈 Boost confidence and academic performance.</p>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Get Academic Support
                    </a>
                </div>
            </div>
            <!-- Slide 7 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100 z-10" style="background-image: url('/images/coding-banner.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50 space-y-2">
                    <h2 class="text-4xl font-bold text-cyan-300">Speak French with Confidence</h2>
                    <p class="mt-4 text-2xl ">Practical French lessons from beginner to fluent.</p>
                        <ul class="space-y-2 mb-4">
                            <li class="text-xl text-cyan-200">🇫🇷 For students, travelers, and professionals.</li>
                            
                        </ul>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Book a French Tutor
                    </a>
                </div>
            </div>
            <!-- Slide 8 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100 z-10" style="background-image: url('/images/b-graphic-design.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50 space-y-2">
                    <h2 class="text-4xl font-bold text-cyan-300">Master Graphic Design</h2>
                    <p class="mt-4 text-2xl ">🎨 Learn graphic design with zero prior experience.</p>
                        <ul class="space-y-2 mb-4">
                            <li class="text-xl text-cyan-200">Perfect for students, creators, marketers & small businesses.</li>
                            
                        </ul>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Start Now
                    </a>
                </div>
            </div>
            <!-- Slide 8 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center transition-opacity duration-1000 opacity-100 z-10" style="background-image: url('/images/b-smartSchool.png');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50 space-y-2">
                    <h2 class="text-4xl font-bold text-cyan-300">Smart School Clubs. Powered by MephEd.</h2>
                    <p class="mt-4 text-2xl ">We help schools run vibrant clubs that build real skills.</p>
                        <ul class="space-y-2 mb-4">
                            <li class="text-xl text-cyan-200">🎯 Coding • Music • Chess • STEM • Taekwondo • French & more.</li>
                            
                        </ul>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Partner With Us
                    </a>
                </div>
            </div>
            <!-- Slide 9 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/b-musicClasses.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"> Music Lessons That Move  <span class="text-cyan-300">You</span></h2>
                    <p class="mt-4 text-2xl text-cyan-100">🎵 Piano, Voice, Guitar, Recorder & More.</p>
                    <p class="mt-4 text-xl text-cyan-200">Expert instructors for kids, teens, and adults.</p>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Book Sessions
                    </a>
                </div>
            </div>
            <!-- Slide 10 -->
            <div class="carousel-item absolute inset-0 bg-cover bg-center opacity-0 transition-opacity duration-1000 z-0" style="background-image: url('/images/b-edtechStrategy.jpeg');">
                <div class="flex flex-col items-center justify-center h-full bg-black text-white px-4 bg-opacity-50">
                    <h2 class="text-4xl font-bold"> Need IT or   <span class="text-cyan-300">EdTech</span> Strategy?</h2>
                    <p class="mt-4 text-2xl text-cyan-100">We help schools and organizations scale with smart digital solutions.</p>
                    <p class="mt-4 text-xl text-cyan-200">🔧 IT consultancy tailored to your goals.</p>
                    <a wire:navigate href="{{ route('client.tutorRequests.create') }}"
                        class="mt-6 inline-flex rounded-lg bg-pink-600 px-4 py-2 text-base font-semibold text-white shadow-sm hover:bg-pink-700">
                        Request a Consultation
                    </a>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <button id="prevSlide" class="carousel-button" style="left: 16px;">‹</button>
        <button id="nextSlide" class="carousel-button" style="right: 16px;">›</button>
    </section>


    <!-- Main Content Section -->

    <main class="container mx-auto py-10 px-6">
        

        <!-- Services Section -->
        <section class="py-10">
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

      //Carousel Script

        const slides = document.querySelectorAll('.carousel-item');
        const totalSlides = slides.length;
        let current = 0;

        const showSlide = index => {
            slides.forEach((slide, i) => {
                slide.classList.remove('opacity-100', 'z-10');
                slide.classList.add('opacity-0', 'z-0');
                if (i === index) {
                    slide.classList.add('opacity-100', 'z-10');
                }
            });
        };

        document.getElementById('prevSlide').addEventListener('click', () => {
            current = (current - 1 + totalSlides) % totalSlides;
            showSlide(current);
        });

        document.getElementById('nextSlide').addEventListener('click', () => {
            current = (current + 1) % totalSlides;
            showSlide(current);
        });

        // Autoplay
        setInterval(() => {
            current = (current + 1) % totalSlides;
            showSlide(current);
        }, 7000); // every 5 seconds

        // Initial display
        showSlide(current);
  </script>

  

   @livewireScripts
</body>
</html>
