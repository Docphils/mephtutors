<div>
    <!-- Services Cards -->
    <section class="py-10">
        <h3 class="text-3xl font-semibold text-gray-800 text-center">Core Offerings</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <img src="/images/home_tutoring.jpg" alt="Home Tutoring" class="w-full h-48 object-cover rounded-t-lg">
                <h4 class="text-xl font-bold text-gray-800 mt-4">Tutoring Services</h4>
                <p class="mt-4 text-gray-600">Personalized home tutoring for all subjects and levels.</p>

            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <img src="/images/coding-classes.jpg" alt="Coding Classes"
                    class="w-full h-48 object-cover rounded-t-lg">
                <h4 class="text-xl font-bold text-gray-800 mt-4">Tech BootCamps</h4>
                <p class="mt-4 text-gray-600">Learn coding from basic to advanced levels with our expert tutors.</p>

            </div>
            <div class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <img src="/images/robotics.jpg" alt="School Clubs" class="w-full h-48 object-cover rounded-t-lg">
                <h4 class="text-xl font-bold text-gray-800 mt-4">School Clubs and Consultancy</h4>
                <p class="mt-4 text-gray-600">Get expert club instructors for your school and other organizations.
                    We offer coding, music, chess, etc.</p>

            </div>
        </div>
    </section>

    <!-- Explore Programs -->
    <section class="py-12">
        <h3 class="text-3xl font-semibold text-gray-800 text-center">Explore Our Programs</h3>
        <p class="text-center text-gray-600 mt-2">From home tutoring to club management, pick what fits you.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
            @foreach ($items as $item)
                <div
                    class="bg-white p-6 rounded-lg shadow hover:shadow-lg hover:scale-105 hover:bg-cyan-50 focus:bg-cyan-50 active:bg-cyan-50 focus:scale-105 active:scale-105 transition duration-300 group">
                    <a href="{{ $item->target === 'bootcamp'
                        ? route('apply.bootcamp', ['serviceItem' => $item->slug])
                        : ($item->target === 'institutions'
                            ? route('apply.crm', ['serviceItem' => $item->slug])
                            : route('apply.tutor', ['serviceItem' => $item->slug])) }}"
                        class="block">
                        <img src="{{ asset('storage/' . $item->image_path ?? '/images/default.jpg') }}"
                            alt="{{ $item->name }}" class="w-full h-40 object-cover rounded">
                        <h4
                            class="mt-4 font-bold text-lg group-hover:text-cyan-900 group-hover:underline group-active:text-cyan-900 group-active:underline group-focus:text-cyan-900 group-focus:underline">
                            {{ $item->name }}</h4>
                    </a>
                    <p class="text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($item->description, 120) }}</p>
                </div>
            @endforeach
        </div>
    </section>
</div>
