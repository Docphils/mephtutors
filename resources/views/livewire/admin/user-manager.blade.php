<div class="p-2 sm:p-6 lg:p-8 bg-cyan-100 min-h-screen w-full">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a wire:navigate href="{{ route('admin.dashboard') }}"
                    class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 bg-cyan-600 text-white rounded-lg hover:bg-cyan-900 transition-colors shadow-sm shrink-0">
                    <i class="fa fa-arrow-left text-xs sm:text-sm"></i>
                </a>
                <div>
                    <h2 class="font-black text-xl sm:text-3xl text-slate-800 tracking-tight leading-tight">
                        Manage <span class="text-cyan-600">Users</span>
                    </h2>
                    <p class="hidden sm:block text-slate-500 text-sm font-medium">Manage user creation, update, and
                        deletion.</p>
                </div>
            </div>
            <button x-on:click="$dispatch('createUser')"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-cyan-700 hover:bg-cyan-800 text-white text-sm font-bold rounded-xl shadow-lg shadow-cyan-200 transition-all active:scale-95 w-full sm:w-auto">
                <i class="fa-solid fa-user-plus mr-2"></i>
                New User
            </button>
        </div>
    </x-slot>

    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-4 sm:mt-8 mb-6 bg-white border border-slate-200 p-2 rounded-2xl shadow-sm">
        <div class="flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
            <nav class="flex overflow-x-auto no-scrollbar items-center p-1 bg-cyan-600 rounded-xl w-full lg:w-auto"
                x-data="{ activeStatus: @entangle('roleFilter') }">
                @foreach (['all' => 'fa-users', 'client' => 'fa-user-graduate', 'tutor' => 'fa-chalkboard-user', 'admin' => 'fa-user-shield'] as $val => $icon)
                    <button wire:click.prevent="$set('roleFilter', '{{ $val }}')"
                        :class="activeStatus === '{{ $val }}' || (activeStatus === '' && '{{ $val }}'
                            === 'all') ? 'bg-white text-cyan-600 shadow-sm' : 'text-white'"
                        class="flex-1 lg:flex-none px-3 sm:px-5 py-2 text-[11px] sm:text-sm font-bold rounded-lg transition-all whitespace-nowrap capitalize">
                        <i class="fa-solid {{ $icon }} mr-1 hidden sm:inline"></i> {{ $val }}
                    </button>
                @endforeach
            </nav>

            <div class="relative w-full lg:flex-1">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input wire:model.live="search" type="text" placeholder="Search users..."
                    class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 text-slate-700 text-sm" />
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-cyan-600">
                        <th
                            class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black text-slate-50 uppercase tracking-widest">
                            User Info</th>
                        <th
                            class="hidden md:table-cell px-6 py-4 text-xs font-black text-slate-50 uppercase tracking-widest">
                            Email</th>
                        <th
                            class="table-cell p-2 sm:px-6 sm:py-4 text-xs font-black text-slate-50 uppercase tracking-widest">
                            Role</th>
                        <th
                            class="px-4 sm:px-6 py-4 text-[10px] sm:text-xs font-black text-slate-50 uppercase tracking-widest text-right">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    @forelse ($users as $user)
                        <tr class="hover:bg-cyan-50 transition-colors">
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-[10px] sm:text-xs shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-slate-800 text-xs sm:text-sm">{{ $user->name }}</span>
                                        <span class="md:hidden text-[10px] text-slate-500">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $user->email }}
                            </td>
                            <td class="table-cell p-2 sm:px-6 sm:py-4 whitespace-nowrap">
                                <span
                                    class="px-2 sm:px-3 py-1 rounded-full text-[6px] sm:text-[9px] font-black uppercase tracking-wider
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : '' }}
                                    {{ $user->role === 'tutor' ? 'bg-cyan-100 text-cyan-700' : '' }}
                                    {{ $user->role === 'client' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end gap-1 sm:gap-2">
                                    <button wire:click="edit({{ $user->id }})"
                                        class="p-2 text-slate-400 hover:text-cyan-600 transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-sm sm:text-base"></i>
                                    </button>
                                    <button wire:click="openDelete({{ $user->id }})"
                                        class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                        <i class="fa-solid fa-trash-can text-sm sm:text-base"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 font-medium">
                                <i class="fa-solid fa-inbox block text-3xl mb-2 opacity-20"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($confirmDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="$set('confirmDelete', false)"></div>
            <div class="relative bg-white rounded-[2rem] p-6 sm:p-8 max-w-sm w-full shadow-2xl text-center">
                <div
                    class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                </div>
                <h3 class="text-xl font-bold text-slate-800">Confirm Delete</h3>
                <p class="text-slate-500 mt-2 text-sm">Delete <strong>{{ $selectedUser->name }}</strong>? This cannot
                    be undone.</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <button wire:click="deleteUser"
                        class="order-1 sm:order-2 flex-1 py-3 bg-red-500 text-white font-bold rounded-xl hover:bg-red-600">Delete</button>
                    <button wire:click="$set('confirmDelete', false)"
                        class="order-2 sm:order-1 flex-1 py-3 bg-slate-100 text-slate-600 font-bold rounded-xl">Cancel</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Create/Edit Form Modal --}}
    @if ($showCreateModal || $showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" wire:click="closeModals"></div>
            <div
                class="relative bg-white rounded-[1.5rem] sm:rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden max-h-[95vh] flex flex-col">
                <div class="bg-cyan-600 p-5 sm:p-6 text-white flex justify-between items-center shrink-0">
                    <h3 class="text-lg sm:text-xl font-bold">
                        <i class="fa-solid {{ $showCreateModal ? 'fa-user-plus' : 'fa-user-gear' }} mr-2"></i>
                        {{ $showCreateModal ? 'Create New User' : 'Edit User Profile' }}
                    </h3>
                    <button wire:click="closeModals" class="hover:rotate-90 transition-transform p-1">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </button>
                </div>

                <div class="p-6 sm:p-8 space-y-4 overflow-y-auto">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Full
                            Name</label>
                        <div class="relative">
                            <i
                                class="fa-solid fa-signature absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="text"
                                wire:model.defer="{{ $showCreateModal ? 'name' : 'selectedUser_name' }}"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 text-sm" />
                        </div>
                        @error('name')
                            <span class="text-red-500 text-xs italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Email
                            Address</label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="email"
                                wire:model.defer="{{ $showCreateModal ? 'email' : 'selectedUser_email' }}"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 text-sm" />
                        </div>
                        @error('email')
                            <span class="text-red-500 text-xs italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Assigned
                            Role</label>
                        <div class="relative">
                            <i class="fa-solid fa-tags absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <select wire:model.defer="{{ $showCreateModal ? 'role' : 'selectedUser_role' }}"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 appearance-none text-sm font-medium">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="tutor">Tutor</option>
                                <option value="client">Client</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Password</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-300"></i>
                            <input type="password" wire:model.defer="password"
                                class="w-full pl-11 pr-4 py-3 bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-cyan-500 text-sm" />
                        </div>
                        @error('password')
                            <span class="text-red-500 text-xs italic">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="bg-slate-50 p-6 flex flex-col sm:flex-row gap-3 shrink-0">
                    <button wire:click="{{ $showCreateModal ? 'store' : 'update' }}"
                        class="flex-1 py-3 bg-cyan-600 text-white font-bold rounded-xl hover:bg-cyan-700 transition-all text-sm">
                        <i class="fa-solid fa-check mr-1"></i> {{ $showCreateModal ? 'Create User' : 'Save Changes' }}
                    </button>
                    <button wire:click="closeModals"
                        class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-100 text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
