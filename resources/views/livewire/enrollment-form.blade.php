<main>
    <section class="relative min-h-[32rem] bg-cover bg-center" style="background-image: url('{{ asset('images/bootcamp-header.jpeg') }}')">
        <div class="absolute inset-0 bg-slate-900/60"></div>
        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 items-start">
                <header class="text-white max-w-xl order-2 lg:order-1">
                    <p class="text-sm font-semibold tracking-wide uppercase text-cyan-200">Beginner-Friendly Tech Skills Bootcamp</p>
                    <h1 class="mt-2 text-3xl sm:text-4xl lg:text-5xl font-black leading-tight">Build practical digital skills for real-world opportunities</h1>
                    <p class="mt-3 text-sm sm:text-base text-slate-100/90">Start your learning journey in software development, product, data, design, and other high-demand tech pathways.</p>
                    <p class="mt-2 text-sm sm:text-base font-semibold text-cyan-200">New cohorts open regularly</p>
                </header>

                <aside class="order-1 lg:order-2">
                    <section class="bg-white text-slate-900 rounded-2xl shadow-2xl p-4 sm:p-5 max-h-[75vh] overflow-y-auto" aria-label="Bootcamp Enrollment Form">
                        <h2 class="text-xl font-bold mb-3">Enroll Now</h2>

                        @if (session()->has('success'))
                            <aside class="bg-green-50 text-green-700 p-2 rounded-lg mb-2 text-sm" id="success-message" role="status">
                                {{ session('success') }}
                            </aside>
                            <a href="https://chat.whatsapp.com/IF4t0n8DcKzEMGS0Tw7d6Q"
                                class="flex w-full mx-auto mb-2 bg-green-500 text-center justify-center text-white px-4 py-2 rounded-lg hover:text-green-50 hover:bg-green-600 transition">
                                Join Community
                            </a>
                        @endif

                        @if (session()->has('error'))
                            <aside class="bg-rose-50 text-rose-700 p-2 rounded-lg mb-2 text-sm" role="alert">
                                {{ session('error') }}
                            </aside>
                        @endif

                        <form wire:submit.prevent="save" class="space-y-2">
                            <div>
                                <label for="service_item_id" class="block text-gray-700 font-semibold">Bootcamp Track</label>
                                <select wire:model.live="service_item_id" id="service_item_id" name="service_item_id"
                                    class="w-full border rounded-lg focus:outline-none focus:ring" required disabled>
                                    <option value="">Select a track</option>
                                    <option value="{{ $service_item_id }}">{{ $service_item_name }}</option>
                                </select>
                                @error('service_item_id')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cohort_id" class="block text-gray-700 font-semibold">Cohort</label>
                                <select wire:model="cohort_id" id="cohort_id" name="cohort_id"
                                    class="w-full border rounded-lg focus:outline-none focus:ring" @disabled(empty($availableCohorts)) required>
                                    <option value="">{{ empty($availableCohorts) ? 'Select a track first' : 'Select a cohort' }}
                                    </option>
                                    @foreach ($availableCohorts as $cohort)
                                        <option value="{{ $cohort->id }}">
                                            {{ $cohort->name }} ({{ $cohort->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('cohort_id')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="name" class="block text-gray-700 font-semibold">Full Name</label>
                                <input wire:model="name" type="text" id="name" name="name"
                                    class="w-full border rounded-lg focus:outline-none focus:ring" required>
                                @error('name')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="w-full">
                                    <label for="email" class="block text-gray-700 font-semibold">Email</label>
                                    <input wire:model="email" type="email" id="email" name="email"
                                        class="w-full border rounded-lg focus:outline-none focus:ring" required>
                                    @error('email')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="w-full">
                                    <label for="phone" class="block text-gray-700 font-semibold">Phone Number</label>
                                    <input wire:model="phone" type="tel" id="phone" name="phone"
                                        class="w-full border rounded-lg focus:outline-none focus:ring" required>
                                    @error('phone')
                                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-gray-700 font-semibold">Address</label>
                                <input wire:model="address" id="address" name="address"
                                    class="w-full border rounded-lg focus:outline-none focus:ring" required />
                                @error('address')
                                    <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit"
                                class="w-full bg-cyan-600 text-white px-4 py-2 rounded-lg hover:text-cyan-50 hover:bg-cyan-700 transition">
                                Enroll
                            </button>

                            <p wire:loading wire:target="save" class="text-cyan-800 text-sm">Submitting...</p>
                        </form>
                    </section>
                </aside>
            </div>
        </div>
    </section>

    <aside class="bg-slate-900 text-white text-center py-3 px-4 font-bold" aria-label="Support Contact">
        To speak with our support staff, call <strong>(+234) 80-628-691-70</strong>
    </aside>

    <section class="bg-slate-50 text-slate-700 py-12 sm:py-16 px-4 sm:px-6">
        <div class="max-w-7xl mx-auto text-center">
            <h2 class="text-2xl sm:text-3xl font-semibold text-slate-900">Why Learn Tech Skills With Us?</h2>
            <ul class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <li class="max-w-sm w-full mx-auto text-left list-none">
                    <article>
                    <i class="fa-solid fa-laptop-code text-cyan-600 mb-2 text-4xl"></i>
                    <p>Start from beginner level and progress into practical, project-based tech training.</p>
                    </article>
                </li>

                <li class="max-w-sm w-full mx-auto text-left list-none">
                    <article>
                    <i class="fa-solid fa-layer-group text-cyan-600 mb-2 text-4xl"></i>
                    <p>Explore in-demand pathways including software, data, product, design, and related digital skills.</p>
                    </article>
                </li>

                <li class="max-w-sm w-full mx-auto text-left list-none">
                    <article>
                    <i class="fa-solid fa-clock text-cyan-600 mb-2 text-4xl"></i>
                    <p>Learn with flexible schedules suitable for students, working professionals, and founders.</p>
                    </article>
                </li>

                <li class="max-w-sm w-full mx-auto text-left list-none">
                    <article>
                    <i class="fa-solid fa-briefcase text-cyan-600 mb-2 text-4xl"></i>
                    <p>Build portfolio-ready work and receive guidance for freelance, internship, and career opportunities.</p>
                    </article>
                </li>
            </ul>
        </div>
    </section>
</main>
