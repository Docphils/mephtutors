<div class="min-h-screen bg-slate-50/50" wire:poll.30s>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div>
                <h2 class="font-black text-3xl text-slate-800 tracking-tight">
                    Admin <span class="text-cyan-600"> Dashboard</span>
                </h2>
                <div class="flex items-center gap-2 mt-1">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <p class="text-slate-500 text-xs font-medium uppercase tracking-wider">Live System Updates</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <div class="relative overflow-hidden bg-white border border-slate-200 rounded-3xl p-8 mb-10 shadow-sm">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-800">
                            Welcome back, <span class="text-cyan-600">{{ explode(' ', $user->name)[0] }}</span>! 👋
                        </h3>
                        <p class="text-slate-500 mt-1">
                            Current Snapshot: <span class="font-bold">{{ $stats['bootcamp_count'] }}</span> Bootcamp
                            signups and
                            <span class="text-cyan-600 font-bold">{{ $stats['unread_messages'] }}</span> unread
                            messages.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a wire:navigate href="{{ route('admin.payments.index') }}"
                            class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-800 transition">View
                            Finances</a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div
                    class="bg-gradient-to-br from-cyan-600 to-cyan-800 p-6 rounded-[2rem] text-white shadow-lg shadow-cyan-200">
                    <p class="text-cyan-100 text-xs font-bold uppercase tracking-widest mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-black">₦{{ number_format($stats['earned_payments']) }}</h3>
                    <p class="mt-2 text-xs opacity-80">{{ $stats['campaigns_sent'] }} Newsletter Campaigns Sent</p>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Pending Items</p>
                            <h3 class="text-3xl font-black text-slate-800">
                                {{ $stats['pending_tutors'] + $stats['pending_requests'] + $stats['pending_interventions'] }}</h3>
                        </div>
                        <div class="bg-orange-100 text-orange-600 p-3 rounded-2xl">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <p class="text-orange-600 text-xs font-bold mt-2">Tutors, Private Requests & Interventions</p>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Bootcamp</p>
                            <h3 class="text-3xl font-black text-slate-800">{{ $stats['bootcamp_count'] }}</h3>
                        </div>
                        <div class="bg-emerald-100 text-emerald-600 p-3 rounded-2xl">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <p class="text-emerald-600 text-xs font-bold mt-2">Total Participants</p>
                </div>

                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Online Sessions</p>
                            <h3 class="text-3xl font-black text-slate-800">{{ $stats['scheduled_meetings'] }}</h3>
                        </div>
                        <div class="bg-emerald-100 text-emerald-600 p-3 rounded-2xl">
                            <i class="fas fa-video"></i>
                        </div>
                    </div>
                    <p class="text-emerald-600 text-xs font-bold mt-2">{{ $stats['completed_meetings'] }} completed</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h4 class="font-bold text-slate-800">Growth Analytics</h4>
                            <p class="text-xs text-slate-400">Comparing Users, Bookings, and Revenue</p>
                        </div>
                        <div id="chartLegend" class="flex gap-4"></div>
                    </div>
                    <div class="h-[350px]" wire:ignore>
                        <canvas id="growthChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                    <h4 class="font-bold text-slate-800 mb-8">Requests Distribution</h4>
                    <div class="h-[250px]" wire:ignore>
                        <canvas id="distributionChart"></canvas>
                    </div>
                    <div class="mt-8 space-y-4">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-cyan-600"></span>
                                <span class="text-sm font-medium text-slate-600">Institutions</span>
                            </div>
                            <span class="font-bold text-slate-800">{{ $stats['new_crm'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-orange-500"></span>
                                <span class="text-sm font-medium text-slate-600">Private Tutors</span>
                            </div>
                            <span class="font-bold text-slate-800">{{ $stats['tutor_requests'] }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-violet-500"></span>
                                <span class="text-sm font-medium text-slate-600">Interventions</span>
                            </div>
                            <span class="font-bold text-slate-800">{{ $stats['intervention_requests'] }}</span>
                        </div>
                        <a wire:navigate href="{{ route('admin.online-meetings') }}"
                            class="flex items-center justify-between p-3 bg-cyan-50 rounded-2xl border border-cyan-100">
                            <span class="text-sm font-bold text-cyan-700">Open Online Sessions</span>
                            <i class="fas fa-arrow-right text-cyan-700"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-slate-800">Upcoming Online Sessions</h4>
                    <a wire:navigate href="{{ route('admin.online-meetings') }}" class="text-xs font-bold text-cyan-600">Manage</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @forelse($upcomingMeetings as $meeting)
                        <div class="p-3 rounded-xl border border-slate-100 bg-slate-50">
                            <p class="text-sm font-bold text-slate-800">{{ $meeting->title }}</p>
                            <p class="text-xs text-slate-500">{{ $meeting->starts_at?->format('M d, Y h:i A') }}</p>
                            <p class="text-xs text-cyan-700">{{ $meeting->client?->name ?? 'N/A' }} / {{ $meeting->tutor?->name ?? 'N/A' }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No upcoming online sessions.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('growthChart');
            const ctx2 = document.getElementById('distributionChart');

            // 1. Growth Chart Implementation with Legend Hero
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                            label: 'New Users',
                            data: @json($chartData['users']),
                            borderColor: '#0891b2',
                            backgroundColor: 'rgba(8, 145, 178, 0.05)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4
                        },
                        {
                            label: 'Bookings',
                            data: @json($chartData['bookings']),
                            borderColor: '#10b981',
                            borderDash: [5, 5],
                            tension: 0.4,
                            pointRadius: 0
                        },
                        {
                            label: 'Revenue (k)',
                            data: @json($chartData['revenue']),
                            borderColor: '#f59e0b',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            pointRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '600'
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#f1f5f9'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });

            // 2. Distribution Chart Implementation
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Institutions', 'Private Tutors', 'Interventions'],
                    datasets: [{
                        data: [{{ $stats['new_crm'] }}, {{ $stats['tutor_requests'] }}, {{ $stats['intervention_requests'] }}],
                        backgroundColor: ['#0891b2', '#f59e0b', '#8b5cf6'],
                        hoverOffset: 10,
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        });
    </script>
</div>
