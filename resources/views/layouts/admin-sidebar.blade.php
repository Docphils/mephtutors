<aside x-cloak :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 flex h-screen h-[100dvh] min-h-0 w-72 shrink-0 transform flex-col overflow-hidden bg-slate-950 text-slate-100 shadow-2xl shadow-slate-950/30 transition-transform duration-300 ease-in-out sm:sticky sm:top-0 sm:z-auto sm:translate-x-0">

    <div class="flex h-16 shrink-0 items-center justify-between border-b border-white/10 px-5">
        <a wire:navigate href="{{ route('admin.dashboard') }}" class="flex items-center gap-3" aria-label="MephEd admin dashboard">
            <img src="{{ asset('images/MephEd.png') }}" alt="MephEd" class="h-9 w-28 object-contain object-left">
            <span class="border-l border-white/15 pl-3 text-[10px] font-bold uppercase tracking-[0.18em] text-cyan-200">Admin</span>
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="Close navigation menu"
            class="grid h-9 w-9 place-items-center rounded-lg text-slate-300 transition hover:bg-white/10 hover:text-white sm:hidden">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- ===== Profile Section ===== -->
    <div class="shrink-0 border-b border-white/10 p-3">
        <livewire:user-profile lazy="on-load" />
    </div>

    <!-- ===== Navigation ===== -->
    <nav @click="if ($event.target.closest('a')) sidebarOpen = false" class="min-h-0 flex-1 space-y-2 overflow-y-auto overscroll-contain px-3 py-4 text-sm">
        @php
            $navLink = 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-slate-300 transition hover:bg-white/10 hover:text-white';
            $activeLink = 'bg-cyan-400/15 text-white ring-1 ring-inset ring-cyan-300/20';
            $navGroups = [
                [
                    'key' => 'people', 'label' => 'People & Requests', 'icon' => 'fa-users',
                    'active' => request()->routeIs('admin.users', 'admin.clientManager', 'admin.tutorProfile', 'tutorRequests.index', 'admin.contactMessages'),
                ],
                [
                    'key' => 'operations', 'label' => 'Learning & Operations', 'icon' => 'fa-layer-group',
                    'active' => request()->routeIs('admin.lessons', 'admin.online-meetings', 'admin.bootcamps', 'admin.serviceCatalog', 'admin.programmeSettings', 'admin.programmeEnquiries', 'admin.crm.index'),
                ],
                [
                    'key' => 'portfolio', 'label' => 'Company Products', 'icon' => 'fa-cubes',
                    'active' => request()->routeIs('admin.products'),
                ],
                [
                    'key' => 'business', 'label' => 'Finance & Communications', 'icon' => 'fa-chart-line',
                    'active' => request()->routeIs('admin.payments.index', 'admin.newsletter', 'admin.testimonials'),
                ],
            ];
        @endphp

        <a wire:navigate href="{{ route('admin.dashboard') }}"
            class="{{ $navLink }} {{ request()->routeIs('admin.dashboard') ? $activeLink : '' }}">
            <i class="fas fa-house w-5 text-cyan-300"></i><span>Dashboard</span>
        </a>

        <div class="rounded-2xl border border-cyan-300/15 bg-gradient-to-br from-cyan-400/10 to-transparent" x-data="{ open: true }">
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-left font-bold text-white transition hover:bg-white/5">
                <span class="flex items-center gap-3"><i class="fas fa-church w-5 text-cyan-300"></i>Church Management</span>
                <i class="fas fa-chevron-down text-[10px] text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-cloak x-show="open" x-transition class="px-2 pb-2">
                <a wire:navigate href="{{ route('admin.churchLeads') }}"
                    class="{{ $navLink }} {{ request()->routeIs('admin.churchLeads') ? $activeLink : '' }}">
                    <i class="fas fa-inbox w-5 text-cyan-300"></i><span class="flex-1">ChurchMS Leads</span>
                    @if (($newChurchLeadCount ?? 0) > 0)
                        <span class="min-w-6 rounded-full bg-amber-300 px-2 py-0.5 text-center text-xs font-black text-amber-950">{{ $newChurchLeadCount > 99 ? '99+' : $newChurchLeadCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        @foreach ($navGroups as $group)
            <section class="rounded-2xl border border-white/[0.07] bg-white/[0.025]" x-data="{ open: {{ $group['active'] ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                    class="flex w-full items-center justify-between rounded-2xl px-3 py-3 text-left font-bold text-slate-200 transition hover:bg-white/5">
                    <span class="flex items-center gap-3"><i class="fas {{ $group['icon'] }} w-5 text-cyan-300"></i>{{ $group['label'] }}</span>
                    <i class="fas fa-chevron-down text-[10px] text-slate-500 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-cloak x-show="open" x-transition class="space-y-1 px-2 pb-2">
                    @if ($group['key'] === 'people')
                        <a wire:navigate href="{{ route('admin.users') }}" class="{{ $navLink }} {{ request()->routeIs('admin.users') ? $activeLink : '' }}"><i class="fas fa-users w-5 text-cyan-300"></i><span>Manage Users</span></a>
                        <a wire:navigate href="{{ route('admin.clientManager') }}" class="{{ $navLink }} {{ request()->routeIs('admin.clientManager') ? $activeLink : '' }}"><i class="fas fa-users w-5 text-cyan-300"></i><span>Manage Clients</span></a>
                        <a wire:navigate href="{{ route('admin.tutorProfile') }}" class="{{ $navLink }} {{ request()->routeIs('admin.tutorProfile') ? $activeLink : '' }}"><i class="fas fa-chalkboard w-5 text-cyan-300"></i><span>Manage Tutor Profiles</span></a>
                        <a wire:navigate href="{{ route('tutorRequests.index') }}" class="{{ $navLink }} {{ request()->routeIs('tutorRequests.index') ? $activeLink : '' }}"><i class="fas fa-book-open w-5 text-cyan-300"></i><span>Manage Requests</span></a>
                        <a wire:navigate href="{{ route('admin.contactMessages') }}" class="{{ $navLink }} {{ request()->routeIs('admin.contactMessages') ? $activeLink : '' }}"><i class="fas fa-envelope w-5 text-cyan-300"></i><span>Contact Messages</span></a>
                    @elseif ($group['key'] === 'operations')
                        <a wire:navigate href="{{ route('admin.lessons') }}" class="{{ $navLink }} {{ request()->routeIs('admin.lessons') ? $activeLink : '' }}"><i class="fas fa-chalkboard-teacher w-5 text-cyan-300"></i><span>Manage Lessons</span></a>
                        <a wire:navigate href="{{ route('admin.online-meetings') }}" class="{{ $navLink }} {{ request()->routeIs('admin.online-meetings') ? $activeLink : '' }}"><i class="fas fa-video w-5 text-cyan-300"></i><span>Online Sessions</span></a>
                        <a wire:navigate href="{{ route('admin.bootcamps') }}" class="{{ $navLink }} {{ request()->routeIs('admin.bootcamps') ? $activeLink : '' }}"><i class="fas fa-user-graduate w-5 text-cyan-300"></i><span>Manage Bootcamps</span></a>
                        <a wire:navigate href="{{ route('admin.serviceCatalog') }}" class="{{ $navLink }} {{ request()->routeIs('admin.serviceCatalog') ? $activeLink : '' }}"><i class="fas fa-list-check w-5 text-cyan-300"></i><span>Service Catalog</span></a>
                        <a wire:navigate href="{{ route('admin.programmeSettings') }}" class="{{ $navLink }} {{ request()->routeIs('admin.programmeSettings') ? $activeLink : '' }}"><i class="fas fa-sliders w-5 text-cyan-300"></i><span>Intervention Settings</span></a>
                        <a wire:navigate href="{{ route('admin.programmeEnquiries') }}" class="{{ $navLink }} {{ request()->routeIs('admin.programmeEnquiries') ? $activeLink : '' }}"><i class="fas fa-envelope-open-text w-5 text-cyan-300"></i><span>Intervention Enquiries</span></a>
                        <a wire:navigate href="{{ route('admin.crm.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.crm.index') ? $activeLink : '' }}"><i class="fas fa-school w-5 text-cyan-300"></i><span>Manage Org. Requests</span></a>
                    @elseif ($group['key'] === 'business')
                        <a wire:navigate href="{{ route('admin.payments.index') }}" class="{{ $navLink }} {{ request()->routeIs('admin.payments.index') ? $activeLink : '' }}"><i class="fas fa-bank w-5 text-cyan-300"></i><span>Manage Payments</span></a>
                        <a wire:navigate href="{{ route('admin.newsletter') }}" class="{{ $navLink }} {{ request()->routeIs('admin.newsletter') ? $activeLink : '' }}"><i class="fas fa-file w-5 text-cyan-300"></i><span>Manage Newsletters</span></a>
                        <a wire:navigate href="{{ route('admin.testimonials') }}" class="{{ $navLink }} {{ request()->routeIs('admin.testimonials') ? $activeLink : '' }}"><i class="fas fa-comment w-5 text-cyan-300"></i><span>Manage Testimonials</span></a>
                    @else
                        <a wire:navigate href="{{ route('admin.products') }}" class="{{ $navLink }} {{ request()->routeIs('admin.products') ? $activeLink : '' }}"><i class="fas fa-cubes w-5 text-cyan-300"></i><span>Products Manager</span></a>
                    @endif
                </div>
            </section>
        @endforeach

    </nav>

    <div class="shrink-0 border-t border-white/10 bg-slate-950/80 p-3">
        <div class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-slate-500">Account</div>
        <a wire:navigate href="{{ route('profile.edit') }}" @click="sidebarOpen = false"
            class="{{ $navLink }} {{ request()->routeIs('profile.edit') ? $activeLink : '' }}">
            <i class="fas fa-cog w-5 text-cyan-300"></i><span>Account Settings</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-slate-300 transition hover:bg-rose-500/10 hover:text-rose-200 focus:outline-none focus:ring-2 focus:ring-rose-300/60">
                <i class="fas fa-right-from-bracket w-5 text-rose-300"></i><span>{{ __('Log Out') }}</span>
            </button>
        </form>
    </div>

</aside>
