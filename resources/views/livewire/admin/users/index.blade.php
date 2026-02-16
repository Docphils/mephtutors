<div>
    <!-- Filter and Search Users -->
    <div class="flex justify-between mb-4 rounded-sm shadow-lg p-2 text-center">
        <div class="flex w-full items-center justify-between gap-4">
            <nav class="font-semibold w-full sm:flex gap-4 text-cyan-50" x-data="{ activeStatus: @entangle('roleFilter') }">
                <button wire:click.prevent="$set('roleFilter', '')" :class="{ 'active': activeStatus === ''}" class="border-r w-full">All Users</button>
                <button wire:click.prevent="$set('roleFilter', 'client')" :class="{ 'active': activeStatus === 'client' }" class="border-r w-full" >Clients</button>
                <button wire:click.prevent="$set('roleFilter', 'tutor')" :class="{ 'active': activeStatus === 'tutor' }" class="border-r w-full">Tutors</button>
                <button wire:click.prevent="$set('roleFilter', 'admin')" :class="{ 'active': activeStatus === 'admin' }" class="border-r w-full">Admins</button>       
            </nav>
            <div class="w-full sm:w-1/2 sm:flex gap-4 ">
                <input wire:click.prevent="$set('roleFilter', 'search')" type="text" wire:model.live="search" placeholder="Search users..." class="input input-bordered w-full max-w-xs rounded-md mb-2 sm:mb-0 text-gray-900"/>
                <button wire:click="create" class="w-full rounded-md shadow-md p-2 text-cyan-50 hover:underline hover:shadow-lg hover:bg-cyan-700">Create New User</button>
            </div> 
        </div>
        
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-600 mb-2">
            {{ session('success') }}
        </div>
    @endif

    <!-- Users Table -->
    <div class="w-full rounded-sm shadow-lg p-4 mb-4">
        <div class="rounded-lg shadow-lg shadow-cyan-700 mb-4" >
            <div class="w-full flex justify-between p-2 text-cyan-100 font-bold text-lg">
                <div class="w-full">Name</div>
                <div class="w-full">Email</div>
                <div class="w-full">Role</div>
                <div class="hidden sm:block w-full">Actions</div>
            </div>
        </div>
        <div>
            @if($roleFilter === ''||$roleFilter === null)
            @foreach ($users as $user)
                <div class="w-full flex justify-between p-2 sm:border-b gap-2">
                    <div class="w-full">{{ $user->name }}</div>
                    <div class="w-full">{{ $user->email }}</div>
                    <div class="hidden sm:block w-full">{{ ucfirst($user->role) }}</div>
                    <div class="w-full hidden sm:flex gap-4">
                        <button wire:click="edit({{ $user->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                        <button wire:click="openDelete({{ $user->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                    </div>
                </div>
                <div class="w-full p-2 flex sm:hidden justify-between items-center border-b">
                    <div class="w-full">Role: <span class="text-cyan-100">{{ ucfirst($user->role) }} </span></div>
                    <div class="w-full flex gap-4">
                        <button wire:click="edit({{ $user->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                        <button wire:click="openDelete({{ $user->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                    </div>
                </div>
            @endforeach
           

            @elseif ($roleFilter === 'client')
            @foreach ($clients as $client)
            <div class="w-full flex justify-between p-2 sm:border-b gap-2">
                <div class="w-full">{{ $client->name }}</div>
                <div class="w-full">{{ $client->email }}</div>
                <div class="hidden sm:block w-full">{{ ucfirst($client->role) }}</div>
                <div class="w-full hidden sm:flex gap-4">
                    <button wire:click="edit({{ $client->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $client->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            <div class="w-full p-2 flex sm:hidden justify-between items-center border-b">
                <div class="w-full">Role: <span class="text-cyan-100">{{ ucfirst($client->role) }} </span></div>
                <div class="w-full flex gap-4">
                    <button wire:click="edit({{ $client->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $client->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            @endforeach

            @elseif ($roleFilter === 'tutor')
            @foreach ($tutors as $tutor)
            <div class="w-full flex justify-between p-2 sm:border-b gap-2">
                <div class="w-full">{{ $tutor->name }}</div>
                <div class="w-full">{{ $tutor->email }}</div>
                <div class="hidden sm:block w-full">{{ ucfirst($tutor->role) }}</div>
                <div class="w-full hidden sm:flex gap-4">
                    <button wire:click="edit({{ $tutor->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $tutor->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            <div class="w-full p-2 flex sm:hidden justify-between items-center border-b">
                <div class="w-full">Role: <span class="text-cyan-100">{{ ucfirst($tutor->role) }} </span></div>
                <div class="w-full flex gap-4">
                    <button wire:click="edit({{ $tutor->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $tutor->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            @endforeach

            @elseif ($roleFilter === 'admin')
            @foreach ($admins as $admin)
            <div class="w-full flex justify-between p-2 sm:border-b gap-2">
                <div class="w-full">{{ $admin->name }}</div>
                <div class="w-full">{{ $admin->email }}</div>
                <div class="hidden sm:block w-full">{{ ucfirst($admin->role) }}</div>
                <div class="w-full hidden sm:flex gap-4">
                    <button wire:click="edit({{ $admin->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $admin->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            <div class="w-full p-2 flex sm:hidden justify-between items-center border-b">
                <div class="w-full">Role: <span class="text-cyan-100">{{ ucfirst($admin->role) }} </span></div>
                <div class="w-full flex gap-4">
                    <button wire:click="edit({{ $admin->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $admin->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            @endforeach

            @elseif ($roleFilter === 'search')
            @foreach ($searchedUser as $user)
            <div class="w-full flex justify-between p-2 sm:border-b gap-2">
                <div class="w-full">{{ $user->name }}</div>
                <div class="w-full">{{ $user->email }}</div>
                <div class="hidden sm:block w-full">{{ ucfirst($user->role) }}</div>
                <div class="w-full hidden sm:flex gap-4">
                    <button wire:click="edit({{ $user->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $user->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            <div class="w-full p-2 flex sm:hidden justify-between items-center border-b">
                <div class="w-full">Role: <span class="text-cyan-100">{{ ucfirst($user->role) }} </span></div>
                <div class="w-full flex gap-4">
                    <button wire:click="edit({{ $user->id }})" class="px-2 py-1 rounded-md bg-blue-500">Edit</button>
                    <button wire:click="openDelete({{ $user->id }})" class="px-2 py-1 rounded-md bg-red-500">Delete</button>
                </div>
            </div>
            @endforeach
            @endif
        </div>
    </div>

    <!-- Pagination Links -->
    {{ $users->links() }}

    <!-- Create Modal -->
    @if ($showCreateModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg">
                    <div class="bg-white p-4 text-gray-900">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Create User</h3>
                        <div class="mt-2">
                            <input type="text" wire:model.defer="name" placeholder="Name" class="rounded-md w-full mb-2" />
                            @error('name') <span class="text-red-600">{{ $message }}</span> @enderror

                            <input type="email" wire:model.defer="email" placeholder="Email" class="rounded-md w-full mb-2" />
                            @error('email') <span class="text-red-600">{{ $message }}</span> @enderror

                            <select wire:model.defer="role" class="rounded-md w-full mb-2 text-gray-900">
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="tutor">Tutor</option>
                                <option value="client">Client</option>
                            </select>
                            @error('role') <span class="text-red-600">{{ $message }}</span> @enderror

                            <input type="password" wire:model.defer="password" placeholder="Password" class="rounded-md w-full mb-2" />
                            @error('password') <span class="text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="bg-cyan-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="store" class="bg-cyan-900 ml-2 px-2 py-1 rounded-md hover:bg-cyan-700 hover:text-gray-100">Create</button>
                        <button wire:click="$set('showCreateModal', false)" class="bg-gray-400 px-2 py-1 rounded-md hover:bg-gray-500 hover:text-gray-50">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if ($showEditModal)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg">
                    <div class="bg-white p-4">
                        <h3 class="w-full bg-cyan-700 pt-4 pb-1 px-3 text-lg leading-6 font-medium text-white">Edit User</h3>
                        <div class="mt-2 text-gray-900">
                            <div class="mb-2">
                                <label for="selectedUser_name" class="block font-medium text-sm text-gray-700">Name</label>
                                <input id="selectedUser_name" type="text"  wire:model.defer="selectedUser_name" value="" placeholder="Mathew Audu" class="rounded-md input input-bordered w-full mb-2" />
                                @error('selectedUser_name') <span class="text-red-600">{{ $message }}</span> @enderror    
                            </div>

                            <div class="mb-2">
                                <label for="selectedUser_email" class="block font-medium text-sm text-gray-700">Email</label>
                                <input id="selectedUser_email" type="email"  wire:model.defer="selectedUser_email" placeholder="sample@example.com" class="rounded-md input input-bordered w-full mb-2" />
                                @error('selectedUser_email') <span class="text-red-600">{{ $message }}</span> @enderror    
                            </div>

                            <div class="mb-2">
                                <label for="selectedUser_role" class="block font-medium text-sm text-gray-700">Role</label>
                                <select  wire:model.defer="selectedUser_role" name="selectedUser_role" id="selectedUser_role" class="form-select rounded-md shadow-sm mt-1 block w-full" required>
                                    <option value="admin" >Admin</option>
                                    <option value="tutor" >Tutor</option>
                                    <option value="client" >Client</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="bg-cyan-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="update" class="bg-cyan-900 ml-2 px-2 py-1 rounded-md hover:bg-cyan-700 hover:text-gray-100">Save</button>
                        <button wire:click="$set('showEditModal', false)" class="bg-gray-400 px-2 py-1 rounded-md hover:bg-gray-500 hover:text-gray-50">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if ($confirmDelete)
        <div class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-lg">
                    <div class="bg-white p-4 text-gray-900">
                        <h3 class="p-4 rounded-sm bg-red-500 text-lg leading-6 font-medium border-b w-full mb-4">Delete User</h3>
                        <p>Are you sure you want to delete <span class="text-black font-semibold">{{$selectedUser->name}}</span>?</p>
                    </div>
                    <div class="bg-red-100 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteUser" class="bg-red-500 px-2 py-1 rounded-md hover:bg-red-600 hover:text-gray-50 ml-2">Delete</button>
                        <button wire:click="$set('confirmDelete', false)" class="bg-gray-400 px-2 py-1 rounded-md hover:bg-gray-500 hover:text-gray-50">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
