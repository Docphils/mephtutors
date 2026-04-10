<section class="py-12 sm:py-16 lg:py-20 bg-gradient-to-r from-slate-100 to-cyan-50 px-4 sm:px-6 lg:px-10">
    <div class="container mx-auto px-2 sm:px-4 lg:px-6">
        <h2 class="text-3xl sm:text-4xl font-bold text-slate-900 mb-8">About MephEd</h2>

        <div class="relative grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div class="relative sm:col-span-2">
                <div class="relative flex flex-col sm:flex-row gap-4">
                    <div class="mb-12">
                        <h3 class="text-xl font-semibold text-slate-900">OUR VISION</h3>
                        <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-3xl mx-auto">
                            To revolutionize education, ensuring everyone, regardless of age or location, has access to
                            exceptional learning resources and opportunities.
                        </p>
                    </div>

                    <div class="mb-12">
                        <h3 class="text-xl font-semibold text-slate-900">OUR MISSION</h3>
                        <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-3xl mx-auto">
                            To leverage advanced technology and innovative solutions to provide top-tier educational
                            resources and services to all learners.
                        </p>
                    </div>
                </div>
                <div class="mb-12">
                    <h3 class="text-xl font-semibold text-slate-900">OUR COMMITTMENTS</h3>
                    <ul class="mt-4 text-base sm:text-lg text-slate-600 max-w-3xl mx-auto list-disc list-inside">
                        <li>Delivering the highest quality, up-to-date learning contents for educators, students, and
                            lifelong learners.</li>
                        <li>Utilizing technology to connect individuals with expert tutors for personalized and
                            effective learning experiences.</li>
                        <li>Developing an efficient system and platform to support educators in delivering outstanding
                            instruction.</li>
                    </ul>
                </div>
            </div>
            <div>
                <img src="{{ asset('images/teacher.png') }}"
                    class="object-cover w-full h-64 sm:h-4/5 border border-4 border-cyan-600 rounded-lg sm:skew-x-6"
                    alt="Teacher">
            </div>
        </div>

        <hr class="border-slate-300 mb-10">
        {{-- <div class="text-center mb-12">
            <h3 class="text-3xl font-semibold text-slate-900">Our Team</h3>
            <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-3xl mx-auto">
                With several years of industry experience and diverse expertise, our team members are committed to
                transforming education by connecting learners of all ages with the ideal tutors to foster their growth
                and success.
            </p>
        </div>

        <div class="flex flex-col md:flex-row justify-center w-full sm:w-3/4 md:w-1/2 mx-auto items-center gap-8 mt-8">
            <div class="relative w-full text-center border border-4 border-cyan-100 bg-white rounded-lg shadow-sm">
                <div class="relative w-full">
                    <img src="{{ asset('images/mercy.jpg') }}" class="object-cover w-full h-56 sm:h-64"
                        alt="Mercy Nwachukwu">
                </div>
                <div class="bg-white px-3">
                    <h4 class="text-xl font-bold text-slate-900">Mercy Nwachukwu</h4>
                    <p class="mt-2 text-base sm:text-lg text-slate-600">Co-founder/COO</p>
                </div>
            </div>
            <div class="relative w-full text-center border border-4 border-cyan-100 bg-white rounded-lg shadow-sm">
                <div class="relative w-full">
                    <img src="{{ asset('images/philip.jpg') }}" class="object-cover w-full h-56 sm:h-64"
                        alt="Philip Nwachukwu">
                </div>
                <div class="bg-white px-3">
                    <h4 class="text-xl font-bold text-slate-900">Philip Nwachukwu</h4>
                    <p class="mt-2 text-base sm:text-lg text-slate-600">Founder/CEO</p>
                </div>
            </div>
        </div> --}}

        <div class="text-center mb-12">
            <h3 class="text-3xl font-semibold text-slate-900">Our Products</h3>
            <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-3xl mx-auto">
                Beyond tutoring and educational support, MephEd is also building practical digital solutions that
                address real
                needs in education and energy. Explore our growing platforms below.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full sm:w-3/4 lg:w-2/3 mx-auto mt-8">
            <div class="relative text-center border-4 border-cyan-100 bg-white rounded-lg shadow-sm overflow-hidden"
                style="box-shadow 0.3s ease;" onmouseover="this.style.boxShadow='0 10px 15px rgba(0,0,0,0.1)';"
                onmouseout="this.style.boxShadow='none';">
                <div class="relative w-full">
                    <img src="{{ asset('images/zaramed.png') }}" class="object-cover w-full h-56 sm:h-64"
                        style="transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)';"
                        onmouseout="this.style.transform='scale(1)';" alt="Zaramed">
                </div>
                <div class="bg-white px-5 py-6">
                    <h4 class="text-xl font-bold text-slate-900">Zaramed</h4>
                    <p class="mt-3 text-sm sm:text-base text-slate-600">
                        A smart school management and CBT platform designed to help schools simplify administration,
                        improve assessments, and strengthen communication across their operations.
                    </p>

                    <a href="https://www.zaramed.ng" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center mt-5 px-5 py-3 bg-cyan-600 text-white font-medium rounded-lg hover:bg-cyan-700 transition">
                        Visit Zaramed
                    </a>
                </div>
            </div>

            <div class="relative text-center border-4 border-cyan-100 bg-white rounded-lg shadow-sm overflow-hidden"
                style="box-shadow 0.3s ease;" onmouseover="this.style.boxShadow='0 10px 15px rgba(0,0,0,0.1)';"
                onmouseout="this.style.boxShadow='none';">
                <div class="relative w-full">
                    <img src="{{ asset('images/solar-sapient.png') }}" class="object-cover w-full h-56 sm:h-64"
                        style="transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)';"
                        onmouseout="this.style.transform='scale(1)';" alt="Solar Sapient">
                </div>
                <div class="bg-white px-5 py-6">
                    <h4 class="text-xl font-bold text-slate-900">Solar Sapient</h4>
                    <p class="mt-3 text-sm sm:text-base text-slate-600">
                        An intelligent solar education, advisory, and audit platform built to help users make better
                        energy decisions with more clarity, confidence, and structure.
                    </p>

                    <a href="https://solar-sapient.mephed.ng" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center mt-5 px-5 py-3 bg-cyan-600 text-white font-medium rounded-lg hover:bg-cyan-700 transition">
                        Visit Solar Sapient
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
