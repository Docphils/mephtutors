<div wire:key="welcome-default" class="space-y-0">
    <livewire:hero-carousel />

    <main class="container mx-auto py-10 px-6 space-y-8">
        <div>
            <livewire:welcome-services />
        </div>

        <section data-reveal class="py-12 rounded-3xl bg-gradient-to-br from-cyan-50 to-white border border-cyan-100">
            <div class="max-w-5xl mx-auto px-4">
                <h3 class="text-3xl font-semibold text-slate-900 text-center">How It Works</h3>
                <p class="text-center text-slate-600 mt-2">Simple steps to find great tutors and learning interventions.</p>

                <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div data-reveal="up" class="vibrant-panel rounded-lg shadow-sm border border-slate-200 p-6 text-center hover-lift">
                        <div class="text-3xl text-cyan-600"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h4 class="mt-4 font-bold">Browse Interventions</h4>
                        <p class="mt-2 text-slate-600">Explore subjects, tutors and formats that suit your needs.</p>
                    </div>
                    <div data-reveal="up" data-reveal-delay="80" class="vibrant-panel rounded-lg shadow-sm border border-slate-200 p-6 text-center hover-lift">
                        <div class="text-3xl text-cyan-600"><i class="fa-solid fa-calendar-check"></i></div>
                        <h4 class="mt-4 font-bold">Book A Lesson</h4>
                        <p class="mt-2 text-slate-600">Schedule flexible sessions, in-person or online.</p>
                    </div>
                    <div data-reveal="up" data-reveal-delay="140" class="vibrant-panel rounded-lg shadow-sm border border-slate-200 p-6 text-center hover-lift">
                        <div class="text-3xl text-cyan-600"><i class="fa-solid fa-chalkboard-user"></i></div>
                        <h4 class="mt-4 font-bold">Learn With Experts</h4>
                        <p class="mt-2 text-slate-600">Work with vetted tutors and track your progress.</p>
                    </div>
                    <div data-reveal="up" data-reveal-delay="200" class="vibrant-panel rounded-lg shadow-sm border border-slate-200 p-6 text-center hover-lift">
                        <div class="text-3xl text-cyan-600"><i class="fa-solid fa-lock"></i></div>
                        <h4 class="mt-4 font-bold">Safe and Secure</h4>
                        <p class="mt-2 text-slate-600">Secure payments, clear policies and responsive support.</p>
                    </div>
                </div>
            </div>
        </section>

        <section data-reveal class="py-12 bg-gradient-to-r from-slate-100 to-cyan-100/60 rounded-2xl">
            <div class="max-w-4xl mx-auto px-4">
                <h3 class="text-3xl font-semibold text-slate-900 text-center">Frequently Asked Questions</h3>
                <p class="text-center text-slate-600 mt-2">Common questions about our services and how to get started.</p>

                <div x-data="{ open: null }" class="mt-8 space-y-3">
                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 1 ? open = null : open = 1"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">How do I book a tutor?</span>
                            <span x-show="open !== 1">+</span>
                            <span x-show="open === 1">-</span>
                        </button>
                        <div x-show="open === 1" x-collapse class="px-6 pb-4 text-slate-600">
                            Browse interventions or request a tutor via our booking form. Choose schedule and lesson format, then confirm payment.
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 2 ? open = null : open = 2"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">Do you offer online lessons?</span>
                            <span x-show="open !== 2">+</span>
                            <span x-show="open === 2">-</span>
                        </button>
                        <div x-show="open === 2" x-collapse class="px-6 pb-4 text-slate-600">
                            Yes, many tutors teach remotely using Zoom, Google Meet or other platforms. Choose the online option when booking.
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 3 ? open = null : open = 3"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">What subjects do you cover?</span>
                            <span x-show="open !== 3">+</span>
                            <span x-show="open === 3">-</span>
                        </button>
                        <div x-show="open === 3" x-collapse class="px-6 pb-4 text-slate-600">
                            We cover school subjects, coding, design, music, languages and school clubs. If you need something else, contact support.
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow">
                        <button @click="open === 4 ? open = null : open = 4"
                            class="w-full text-left px-6 py-4 flex justify-between items-center">
                            <span class="font-medium">Can schools partner with MephEd?</span>
                            <span x-show="open !== 4">+</span>
                            <span x-show="open === 4">-</span>
                        </button>
                        <div x-show="open === 4" x-collapse class="px-6 pb-4 text-slate-600">
                            Yes, we provide club instructors, EdTech strategy and bespoke interventions. Use the partner options on our platform to get started.
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section data-reveal="left" class="py-20 bg-gradient-to-r from-slate-100 to-cyan-50 rounded-2xl">
            <div class="container mx-auto text-center">
                <h3 class="text-3xl font-semibold text-slate-900">About Us</h3>
                <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">
                    MephEd is dedicated to providing top-notch home tutoring and innovative coding and robotics classes.
                    Our experienced tutors and comprehensive curriculum ensure that every student achieves their full potential.
                </p>
            </div>
        </section>
    </main>

    <livewire:testimonials.home-testimonials />

    <section data-reveal class="px-8 sm:px-20 py-20 bg-gradient-to-r from-slate-900 to-cyan-900 text-white">
        <div class="container sm:flex justify-between items-center mx-auto text-center gap-4">
            <div class="text-justify my-4" data-reveal="left">
                <h3 class="text-3xl font-semibold text-cyan-100">Become a Tutor</h3>
                <p class="mt-4 text-lg text-slate-200 max-w-2xl mx-auto">
                    Join our team of professional tutors reshaping the horizons of tutelage in our world. Our outstanding
                    community of exceptional tutors and teams are ever ready to support you for maximum impact and efficiency.
                </p>
                <div class="mt-4">
                    <a wire:navigate href="{{ route('register') }}"
                        class="bg-cyan-500 text-lg text-white px-4 py-2 rounded-lg hover:bg-cyan-600 transition">
                        Join Us
                    </a>
                </div>
            </div>
            <div data-reveal="right" class="relative sm:flex mx-auto gap-4 text-center justify-between">
                <div class="relative">
                    <img src="{{ asset('images/teacher2.png') }}"
                        class="object-cover w-full h-48 border border-double border-cyan-300 bg-white border-4 rounded-full"
                        alt="Tutor">
                </div>
                <div class="flex items-start border-2 h-48 bg-cyan-100 rounded-full">
                    <img src="{{ asset('images/teacher.png') }}" class="object-cover w-full h-64 border-b" alt="Tutor">
                </div>
            </div>
        </div>
    </section>

    <section data-reveal class="py-20 bg-gradient-to-r from-slate-100 to-cyan-50">
        <div class="container mx-auto">
            <h3 class="text-3xl font-semibold text-slate-900 text-center">Contact Us</h3>
            <div class="mt-12 max-w-lg mx-auto bg-white p-8 rounded-lg shadow-sm border border-slate-200">
                <livewire:contact-form lazy="on-load" />
            </div>
        </div>
    </section>
</div>
