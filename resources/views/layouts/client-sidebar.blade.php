<aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="
        fixed sm:static
        inset-y-0 left-0
        z-50 sm:z-auto
        w-72
        bg-gradient-to-b from-cyan-700 to-cyan-900
        text-white
        shadow-xl
        transform
        sm:translate-x-0
        transition-transform duration-300 ease-in-out
        flex flex-col
    ">

    <!-- ===== Mobile Header ===== -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-cyan-600 sm:hidden">
        <h2 class="font-semibold text-lg">Menu</h2>
        <button @click="sidebarOpen = false" class="text-xl">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ===== Desktop Profile Section ===== -->
    <div class="hidden sm:block px-6 py-6 border-b border-cyan-600">
        <livewire:user-profile lazy="on-load" />
    </div>

    <!-- ===== Navigation ===== -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">

        <a wire:navigate href="{{ route('client.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('client.dashboard') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-house w-5 text-cyan-300"></i>
            <span>Dashboard</span>
        </a>

        <a wire:navigate href="{{ route('client.tutorRequests.manager') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('client.tutorRequests.manager') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-paper-plane w-5 text-cyan-300"></i>
            <span>Tutor Requests</span>
        </a>

        <a wire:navigate href="{{ route('client.crm.manager') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('client.crm.manager') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-school w-5 text-cyan-300"></i>
            <span>Institution Bookings</span>
        </a>

        <a wire:navigate href="{{ route('client.lessons') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('client.lessons') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-chalkboard-teacher w-5 text-cyan-300"></i>
            <span>Manage Lessons</span>
        </a>

    </nav>

    <!-- ===== Footer ===== -->
    <div class="px-6 py-4 border-t border-cyan-600">
        @livewire('newsletter-subscription')
    </div>

</aside>
