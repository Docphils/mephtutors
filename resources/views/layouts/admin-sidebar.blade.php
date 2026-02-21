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

    <!--Logo-->
    <div class="flex items-center justify-center h-16 border-b border-cyan-600">
        <img src="{{ asset('images/MephEd.png') }}" alt="Logo Image" class="object-cover h-6 sm:h-8 w-24 sm:w-32">
    </div>

    <!-- ===== Mobile Header ===== -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-cyan-600 sm:hidden">
        <h2 class="font-semibold text-lg">Menu</h2>
        <button @click="sidebarOpen = false" class="text-xl">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ===== Desktop Profile Section ===== -->
    <div class="hidden sm:block p-3 border-b border-cyan-600">
        <livewire:user-profile lazy="on-load" />
    </div>

    <!-- ===== Navigation ===== -->
    <nav class="flex-1 px-4 py-6 overflow-y-auto text-sm">

        <a wire:navigate href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.dashboard') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-house w-5 text-cyan-300"></i>
            <span>Dashboard</span>
        </a>

        <a wire:navigate href="{{ route('admin.users') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.users') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-users w-5 text-cyan-300"></i>
            <span>Manage Users</span>
        </a>

        <a wire:navigate href="{{ route('tutorRequests.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutorRequests.index') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-book-open w-5 text-cyan-300"></i>
            <span>Manage Requests</span>
        </a>

        <a wire:navigate href="{{ route('admin.lessons') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.lessons') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-chalkboard-teacher w-5 text-cyan-300"></i>
            <span>Manage Lessons</span>
        </a>

        <a wire:navigate href="{{ route('admin.payments.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.payments.index') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-bank w-5 text-cyan-300"></i>
            <span>Manage Payments</span>
        </a>

        <a wire:navigate href="{{ route('admin.tutorProfile') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.tutorProfile') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-chalkboard w-5 text-cyan-300"></i>
            <span>Manage Tutor Profiles</span>
        </a>

        <a wire:navigate href="{{ route('admin.newsletter') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.newsletter') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-file w-5 text-cyan-300"></i>
            <span>Manage Newsletters</span>
        </a>

        <a wire:navigate href="{{ route('admin.crm.index') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.crm.index') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-school w-5 text-cyan-300"></i>
            <span>CRM & Clubs</span>
        </a>

        <a wire:navigate href="{{ route('admin.testimonials') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.testimonials') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-comment w-5 text-cyan-300"></i>
            <span>Manage Testimonials</span>
        </a>
        <a wire:navigate href="{{ route('admin.clientManager') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.clientManager') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-users w-5 text-cyan-300"></i>
            <span>Manage Clients</span>
        </a>
        <a wire:navigate href="{{ route('admin.contactMessages') }}"
            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('admin.contactMessages') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-envelope w-5 text-cyan-300"></i>
            <span>Contact Messages</span>
        </a>

    </nav>

    <!-- ===== Footer ===== -->
    <div class="px-6 py-4 border-t border-cyan-600">
        ----
    </div>

</aside>
