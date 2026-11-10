<div class="min-h-full bg-slate-50/70">
    <x-slot name="header">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-cyan-700">MephEd administration</p>
            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Dashboard</h1>
        </div>
    </x-slot>

    <main class="mx-auto max-w-7xl space-y-6 px-4 py-5 sm:space-y-8 sm:px-6 sm:py-8 lg:px-8">
        <section
            class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-cyan-950 to-cyan-800 px-5 py-7 text-white shadow-xl shadow-cyan-950/10 sm:px-8 sm:py-9">
            <div class="absolute -right-12 -top-20 -z-10 h-64 w-64 rounded-full bg-cyan-400/20 blur-3xl"
                aria-hidden="true"></div>
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-cyan-200">{{ $dashboardDate }}</p>
                    <h2 class="mt-2 text-2xl font-black tracking-tight sm:text-3xl">Welcome back, {{ $greetingName }}.
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-6 text-cyan-50/80 sm:text-base">Here’s your operational
                        snapshot: {{ number_format($attentionCount) }} items need attention.</p>
                </div>
                <a wire:navigate href="{{ route('admin.contactMessages') }}"
                    class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 shadow-sm transition hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-cyan-900 sm:w-auto">
                    <i class="fas fa-inbox text-cyan-800" aria-hidden="true"></i> Review inbox
                    @if ($stats['unread_messages'] > 0)
                        <span
                            class="rounded-full bg-cyan-100 px-2 py-0.5 text-xs text-cyan-900">{{ $stats['unread_messages'] }}</span>
                    @endif
                </a>
            </div>
        </section>

        <section aria-label="Key metrics" class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 xl:grid-cols-5">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total users</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($stats['total_users']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ number_format($stats['new_users_week']) }} joined in
                            the last 7 days</p>
                    </div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-cyan-50 text-cyan-800"><i
                            class="fas fa-users" aria-hidden="true"></i></span>
                </div>
                <div class="mt-4 flex gap-3 border-t border-slate-100 pt-3 text-xs font-medium text-slate-600">
                    <span>{{ number_format($stats['total_clients']) }} learners</span><span
                        aria-hidden="true">·</span><span>{{ number_format($stats['total_tutors']) }} tutors</span>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Business income</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            ₦{{ number_format((float) $stats['income_total']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">₦{{ number_format((float) $stats['income_month']) }}
                            received this month</p>
                    </div>
                    <span
                        class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-700"><i
                            class="fas fa-wallet" aria-hidden="true"></i></span>
                </div>
                <a wire:navigate href="{{ route('admin.income.index') }}"
                    class="mt-4 inline-flex items-center gap-1 border-t border-slate-100 pt-3 text-xs font-bold text-cyan-800 hover:text-cyan-950">View
                    income records <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i></a>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Tutor earnings</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            ₦{{ number_format((float) $stats['tutor_earned']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">
                            ₦{{ number_format((float) $stats['tutor_earned_month']) }} earned this month</p>
                    </div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-sky-50 text-sky-700"><i
                            class="fas fa-wallet" aria-hidden="true"></i></span>
                </div>
                <a wire:navigate href="{{ route('admin.payments.index') }}"
                    class="mt-4 inline-flex items-center gap-1 border-t border-slate-100 pt-3 text-xs font-bold text-cyan-800 hover:text-cyan-950">View
                    tutor payments <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i></a>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Needs attention</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($attentionCount) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Requests, tutor reviews and unread messages</p>
                    </div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-50 text-amber-700"><i
                            class="fas fa-bell" aria-hidden="true"></i></span>
                </div>
                <div
                    class="mt-4 flex flex-wrap gap-x-3 gap-y-1 border-t border-slate-100 pt-3 text-xs font-medium text-slate-600">
                    <span>{{ $stats['pending_tutors'] }} tutor reviews</span><span>{{ $pendingRequestCount }}
                        requests</span><span>{{ $stats['unread_messages'] }} messages</span>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Learning sessions</p>
                        <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                            {{ number_format($stats['active_bookings']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">Active bookings · {{ $stats['new_bookings_week'] }} new
                            this week</p>
                    </div>
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-violet-50 text-violet-700"><i
                            class="fas fa-book-open" aria-hidden="true"></i></span>
                </div>
                <div class="mt-4 flex gap-3 border-t border-slate-100 pt-3 text-xs font-medium text-slate-600">
                    <span>{{ $stats['scheduled_meetings'] }} scheduled meetings</span><span
                        aria-hidden="true">·</span><span>{{ $stats['completed_bookings'] }} completed bookings</span>
                </div>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6 xl:col-span-2">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Signups & bookings</h2>
                        <p class="mt-1 text-sm text-slate-500">Daily activity over the last seven days</p>
                    </div>
                    <div class="flex flex-wrap gap-4 text-xs font-semibold text-slate-600" aria-label="Chart legend">
                        <span class="inline-flex items-center gap-2"><span
                                class="h-2.5 w-2.5 rounded-full bg-cyan-600"></span>New users</span>
                        <span class="inline-flex items-center gap-2"><span
                                class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Bookings</span>
                    </div>
                </div>
                <div class="mt-5 overflow-hidden" role="img"
                    aria-label="Line chart showing daily new users and bookings for the last seven days">
                    <svg viewBox="0 0 {{ $chart['width'] }} {{ $chart['height'] }}"
                        class="h-56 w-full overflow-visible sm:h-64" preserveAspectRatio="none" aria-hidden="true">
                        @foreach ($chart['gridLines'] as $gridLine)
                            <line x1="{{ $chart['padding'] }}" y1="{{ $gridLine }}"
                                x2="{{ $chart['width'] - $chart['padding'] }}" y2="{{ $gridLine }}"
                                stroke="#e2e8f0" stroke-dasharray="3 5" />
                        @endforeach
                        <polyline points="{{ $chart['userPolyline'] }}" fill="none" stroke="#0891b2"
                            stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                            vector-effect="non-scaling-stroke" />
                        <polyline points="{{ $chart['bookingPolyline'] }}" fill="none" stroke="#10b981"
                            stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                            vector-effect="non-scaling-stroke" />
                        @foreach ($chart['userPoints'] as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#0891b2"
                                vector-effect="non-scaling-stroke">
                                <title>{{ $point['date'] }}: {{ $point['value'] }} new users</title>
                            </circle>
                        @endforeach
                        @foreach ($chart['bookingPoints'] as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#10b981"
                                vector-effect="non-scaling-stroke">
                                <title>{{ $point['date'] }}: {{ $point['value'] }} bookings</title>
                            </circle>
                        @endforeach
                    </svg>
                </div>
                <div class="mt-2 grid grid-cols-7 text-center text-[11px] font-medium text-slate-500 sm:text-xs">
                    @foreach ($chartData['labels'] as $index => $label)
                        <span>{{ $label }}<span class="hidden sm:inline">,
                                {{ $chartData['dates'][$index] }}</span></span>
                    @endforeach
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-black text-slate-900">Operational pipeline</h2>
                        <p class="mt-1 text-sm text-slate-500">Items awaiting action</p>
                    </div>
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-700"><i
                            class="fas fa-list-check" aria-hidden="true"></i></span>
                </div>
                <div class="mt-5 space-y-4">
                    @foreach ([['Tutoring requests', $stats['pending_requests'], 'admin.lessons', 'text-amber-700', 'bg-amber-500'], ['Tutor applications', $stats['pending_tutors'], 'admin.tutorProfile', 'text-cyan-800', 'bg-cyan-600'], ['Programme enquiries', $stats['pending_interventions'], 'admin.programmeEnquiries', 'text-violet-700', 'bg-violet-500'], ['Institution leads', $stats['new_crm'], 'admin.crm.index', 'text-emerald-700', 'bg-emerald-500']] as [$label, $count, $route, $textClass, $barClass])
                        <a wire:navigate href="{{ route($route) }}"
                            class="group block rounded-xl p-2 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">
                            <span class="flex items-center justify-between gap-3 text-sm">
                                <span
                                    class="font-semibold text-slate-700 group-hover:text-slate-950">{{ $label }}</span>
                                <span class="font-black {{ $textClass }}">{{ number_format($count) }}</span>
                            </span>
                            <span class="mt-2 block h-1.5 overflow-hidden rounded-full bg-slate-100"><span
                                    class="block h-full rounded-full {{ $barClass }}"
                                    style="width: {{ min(100, max(8, $count * 8)) }}%"></span></span>
                        </a>
                    @endforeach
                </div>
                <a wire:navigate href="{{ route('admin.programmeEnquiries') }}"
                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">View
                    all requests <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></a>
            </article>
        </section>

        <section aria-labelledby="quick-links-heading">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <h2 id="quick-links-heading" class="text-lg font-black text-slate-900">Quick links</h2>
                    <p class="mt-1 text-sm text-slate-500">Jump straight to frequently used admin tools</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ([['Users', 'Manage user accounts', 'admin.users', 'fa-users', 'bg-cyan-50 text-cyan-800'], ['Tutor profiles', 'Review tutor applications', 'admin.tutorProfile', 'fa-chalkboard-user', 'bg-violet-50 text-violet-700'], ['Payments', 'Review payment records', 'admin.payments.index', 'fa-credit-card', 'bg-emerald-50 text-emerald-700'], ['Programme enquiries', 'Manage learner enquiries', 'admin.programmeEnquiries', 'fa-graduation-cap', 'bg-amber-50 text-amber-700'], ['Messages', 'Read contact submissions', 'admin.contactMessages', 'fa-envelope', 'bg-rose-50 text-rose-700'], ['Online meetings', 'Manage learning sessions', 'admin.online-meetings', 'fa-video', 'bg-sky-50 text-sky-700'], ['eBook orders', 'Review digital orders', 'admin.ebooks.orders', 'fa-book-open', 'bg-indigo-50 text-indigo-700'], ['Blog studio', 'Create and publish articles', 'admin.blog.index', 'fa-newspaper', 'bg-teal-50 text-teal-700']] as [$label, $description, $route, $icon, $colorClass])
                    <a wire:navigate href="{{ route($route) }}"
                        class="group flex min-h-24 items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600 sm:min-h-28 sm:gap-4 sm:p-4">
                        <span
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $colorClass }} sm:h-12 sm:w-12"><i
                                class="fas {{ $icon }}" aria-hidden="true"></i></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold text-slate-900">{{ $label }}</span>
                            <span
                                class="mt-1 hidden text-xs leading-5 text-slate-500 sm:block">{{ $description }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-black text-slate-900">Recent messages</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $stats['unread_messages'] }} unread in your inbox
                        </p>
                    </div>
                    <a wire:navigate href="{{ route('admin.contactMessages') }}"
                        class="rounded-lg px-3 py-2 text-xs font-bold text-cyan-800 hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">Open
                        inbox <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentMessages as $message)
                        <a wire:navigate href="{{ route('admin.contactMessages') }}"
                            class="flex gap-3 px-5 py-4 transition hover:bg-slate-50 sm:px-6">
                            <span
                                class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $message['is_read'] ? 'bg-slate-200' : 'bg-cyan-500' }}"
                                aria-label="{{ $message['is_read'] ? 'Read' : 'Unread' }}"></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1"><span
                                        class="truncate text-sm font-bold text-slate-900">{{ $message['name'] }}</span><time
                                        class="text-xs text-slate-400">{{ $message['created_label'] }}</time></span>
                                <span class="mt-1 block truncate text-xs text-slate-500">{{ $message['email'] }} ·
                                    {{ $message['message'] }}</span>
                            </span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">No contact messages yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                    <div>
                        <h2 class="font-black text-slate-900">Upcoming online meetings</h2>
                        <p class="mt-1 text-xs text-slate-500">{{ $stats['scheduled_meetings'] }} currently scheduled
                        </p>
                    </div>
                    <a wire:navigate href="{{ route('admin.online-meetings') }}"
                        class="rounded-lg px-3 py-2 text-xs font-bold text-cyan-800 hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">Manage
                        <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($upcomingMeetings as $meeting)
                        <div class="flex gap-4 px-5 py-4 sm:px-6">
                            <div
                                class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-cyan-50 text-center text-cyan-900">
                                <span><span
                                        class="block text-[10px] font-bold uppercase">{{ $meeting['month'] }}</span><span
                                        class="block text-lg font-black leading-5">{{ $meeting['day'] }}</span></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-bold text-slate-900">{{ $meeting['title'] }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $meeting['starts_label'] }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">{{ $meeting['client_name'] }} <span
                                        aria-hidden="true">·</span> {{ $meeting['tutor_name'] }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-500">No upcoming meetings scheduled.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-black text-slate-900">Recent eBook orders</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ number_format($stats['ebook_orders']) }} total ·
                        {{ number_format($stats['paid_ebook_orders']) }} paid</p>
                </div>
                <a wire:navigate href="{{ route('admin.ebooks.orders') }}"
                    class="rounded-lg px-3 py-2 text-xs font-bold text-cyan-800 hover:bg-cyan-50">View all orders <i
                        class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentEbookOrders as $order)
                    <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 sm:px-6">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">
                                {{ $order->ebook?->title ?? 'eBook unavailable' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $order->buyer_name }} ·
                                {{ $order->created_at->diffForHumans() }}</p>
                        </div>
                        <span
                            class="text-right text-xs font-semibold text-slate-600">₦{{ number_format($order->amount_kobo / 100) }}
                            · {{ ucfirst($order->status->value) }}</span>
                    </div>
                @empty
                    <p class="px-5 py-6 text-center text-sm text-slate-500">No eBook orders yet.</p>
                @endforelse
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-black text-slate-900">Latest registrations</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $stats['new_users_today'] }} new users today</p>
                </div>
                <a wire:navigate href="{{ route('admin.users') }}"
                    class="rounded-lg px-3 py-2 text-xs font-bold text-cyan-800 hover:bg-cyan-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-cyan-600">View
                    users <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>
            </div>
            <div class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-5">
                @forelse ($recentUsers as $registeredUser)
                    <div class="flex min-w-0 items-center gap-3 px-5 py-4">
                        <span
                            class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-100 text-sm font-black text-slate-600">{{ $registeredUser['initial'] }}</span>
                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-bold text-slate-900">{{ $registeredUser['name'] }}</span>
                            <span class="mt-0.5 block text-xs capitalize text-slate-500">{{ $registeredUser['role'] }}
                                · {{ $registeredUser['created_label'] }}</span>
                        </span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-sm text-slate-500">No registrations yet.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
