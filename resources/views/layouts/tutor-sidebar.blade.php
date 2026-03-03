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
        <a wire:navigate href="{{ route('welcome') }}">
            <img src="{{ asset('images/MephEd.png') }}" alt="Logo Image" class="object-cover h-6 sm:h-8 w-24 sm:w-32">
        </a>
    </div>

    <div class="text-center my-4">
        <div class="flex justify-center text-sm">
            <img src="{{ asset('storage/' . $tutorProfile?->image) }}" alt="Profile image"
                class="h-10 w-10 rounded-full object-cover border-2 border-white shadow-sm shadow-white">
        </div>
        <p class="font-semibold">{{ $tutorProfile?->fullName }}</p>
        <p class="text-xs text-green-400">{{ strToUpper($tutorProfile?->user->role) }}</p>

    </div>

    <!-- ===== Mobile Header ===== -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-cyan-600 sm:hidden">
        <h2 class="font-semibold text-lg">Menu</h2>
        <button @click="sidebarOpen = false" class="text-xl">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ===== Navigation ===== -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">

        <a wire:navigate href="{{ route('tutor.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutor.dashboard') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-house w-5 text-cyan-300"></i>
            <span>Dashboard</span>
        </a>

        <a wire:navigate href="{{ route('tutor.lessons') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutor.lessons') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-book text-cyan-100 w-6 hidden md:block"></i><span>My Lessons</span>
        </a>

        <a wire:navigate href="{{ route('tutor.institution-assignments') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutor.institution-assignments') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-code text-cyan-100 w-6 hidden md:block"></i><span>Institution Assignments</span>
        </a>

        <a wire:navigate href="{{ route('tutor.payments') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutor.payments') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-money-bill-wave text-cyan-100 w-6 hidden md:block"></i><span>Manage Payments</span>
        </a>

        <a wire:navigate href="{{ route('tutor.tutor-profile') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('tutor.tutor-profile') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-user text-cyan-100 w-6 hidden md:block"></i><span>Manage Profile</span>
        </a>
        <a wire:navigate href="{{ route('welcome') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition {{ request()->routeIs('client.crm.manager') ? 'bg-cyan-600' : '' }}">
            <i class="fas fa-door-open w-5 text-cyan-300"></i>
            <span>Home Page</span>
        </a>

        @if ($approvedTutorProfile)
            <a href="https://chat.whatsapp.com/Gum1YqOp0G31WlyyokKxE0" target="_blank"
                class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 transition">
                <i class="fab fa-whatsapp text-green-300 w-6 hidden md:block"></i><span class="">Tutor
                    community</span>
            </a>
        @endif


        <!-- ===== Footer ===== -->
        <div class="border-t border-cyan-600 my-4 text-sm">
            <!-- Settings Dropdown -->
            <div class="items-center w-full bg-slate-200 rounded-lg p-3 mt-4 text-gray-700">
                <a wire:navigate href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 hover:text-cyan-100 transition {{ request()->routeIs('profile.edit') ? 'bg-cyan-600' : '' }}">
                    <i class="fas fa-cog w-5 text-cyan-300"></i>
                    <span>Account Settings</span>
                </a>
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-cyan-600 hover:text-cyan-100 transition">
                    @csrf
                    <i class="fa-solid fa-right-from-bracket w-5 text-cyan-300"></i>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                                this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </a>
                </form>
            </div>
        </div>
    </nav>

</aside>
