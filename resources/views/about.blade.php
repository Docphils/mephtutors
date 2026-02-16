<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MephEd</title>
     <!-- Fonts -->
     <link rel="preconnect" href="https://fonts.bunny.net">
     <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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

    <!-- About Us Section -->
    <section class="py-20 bg-gradient-to-r from-cyan-100 to-cyan-200">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-gray-800 mb-8">About MephEd</h2>

            <div class="relative sm:grid sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div class="relative sm:col-span-2">
                    <div class="relative sm:flex gap-4">
                        <div class="mb-12">
                            <h3 class="text-xl font-semibold text-gray-800">OUR VISION</h3>
                            <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">To revolutionize education, ensuring everyone, regardless of age or location, has access to exceptional learning resources and opportunities.</p>
                        </div>

                        <div class="mb-12">
                            <h3 class="text-xl font-semibold text-gray-800">OUR MISSION</h3>
                            <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">To leverage advanced technology and innovative solutions to provide top-tier educational resources and services to all learners.</p>
                        </div>
                    </div>
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold text-gray-800">OUR COMMITTMENTS</h3>
                        <ul class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto list-disc list-inside">
                            <li>Delivering the highest quality, up-to-date learning contents for educators, students, and lifelong learners.</li>
                            <li>Utilizing technology to connect individuals with expert tutors for personalized and effective learning experiences.</li>
                            <li>Developing an efficient system and platform to support educators in delivering outstanding instruction.</li>
                        </ul>
                    </div>
                </div>
                <div class="">
                    <img src="/images/teacher.png" class="object-cover w-full h-4/5 border border-4 border-pink-600 rounded-lg skew-x-6 " alt="">
                </div>
            </div>

            <!-- Our Team Section -->
            <hr class="border-white mb-10">
            <div class="text-center mb-12">
                <h3 class="text-3xl font-semibold text-gray-800">Our Team</h3>
                <p class="mt-4 text-lg text-gray-600 max-w-3xl mx-auto">With several years of industry experience and diverse expertise, our team members are committed to transforming education by connecting learners of all ages with the ideal tutors to foster their growth and success.</p>
            </div>

            <div class="flex flex-col md:flex-row justify-center sm:w-1/2 w-3/4 mx-auto items-center gap-8 mt-8">
                <div class="relative w-full text-center border border-4 border-white bg-cyan-400">
                    <div class="relative w-full">
                        <img src="/images/mercy.jpg" class="object-cover w-full h-64 ">
                    </div>
                    <div class="bg-white px-3">
                        <h4 class="text-xl font-bold text-gray-800">Mercy Nwachukwu</h4>
                        <p class="mt-2 text-lg text-gray-600">Co-founder/COO</p>
                    </div>                    
                    
                </div>
                <div class="relative w-full text-center border border-4 border-white bg-cyan-400">
                    <div class="relative w-full">
                        <img src="/images/philip.jpg" class="object-cover w-full h-64 ">
                    </div>
                    <div class="bg-white px-3">
                        <h4 class="text-xl font-bold text-gray-800">Philip Nwachukwu</h4>
                        <p class="mt-2 text-lg text-gray-600">Founder/CEO</p>
                    </div>
                    
                </div>
                
            </div>
        </div>
    </section>

    @include('layouts.footer')
    @livewireScripts
</body>
</html>
