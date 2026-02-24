<?php

namespace App\Livewire\Admin;

use App\Mail\UserCreatedEmail;
use Livewire\Component;
use App\Models\User;
use App\Models\Booking;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Events\Registered;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('MephEd - User Management')]
class UserManager extends Component
{
    use WithPagination;

    public $search = '';
    public $roleFilter = 'all'; 
    public $selectedUser = null;
    public $confirmDelete = false;
    public $showEditModal = false;
    public $showCreateModal = false;

    public $name;
    public $email;
    public $role;
    public $password;
    public $selectedUser_name;
    public $selectedUser_email;
    public $selectedUser_role;

    protected $rules = [
        'selectedUser_name' => 'required|string|max:255',
        'selectedUser_email' => 'required|email|unique:users,email',
        'selectedUser_role' => 'required|in:admin,tutor,client',
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'role' => 'required|in:admin,tutor,client',
        'password' => 'required|min:8',
    ];

    protected $listeners = [
        "confirmDelete" => "confirmDelete",
        "deleteUser" => "deleteUser",
        "createUser" => "create",
    ];

    public function mount()
    {
        Gate::authorize('Admin');
        
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        $searchedUser = $query->latest()->paginate(20);
        $users = User::latest()->paginate(20);
        $clients = User::where('role', 'client')->latest()->paginate(20);
        $tutors = User::where('role', 'tutor')->latest()->paginate(20);
        $admins = User::where('role', 'admin')->latest()->paginate(20);

        return view('livewire.admin.user-manager', compact('users', 'clients', 'tutors', 'admins', 'searchedUser'));
    }

    public function resetFilters()
    {
        $this->reset('search', 'roleFilter', 'selectedUser');
    }

    public function edit(User $user)
    {
        $this->selectedUser = $user;
        $this->selectedUser_name = $user->name;
        $this->selectedUser_email = $user->email;
        $this->selectedUser_role = $user->role;
        $this->showEditModal = true;
    }

    public function update()
    {
        // Validate all fields for the selected user
        $this->validate([
            'selectedUser_name' => 'required|string|max:255',
            'selectedUser_email' => 'required|email|unique:users,email,' . $this->selectedUser->id,
            'selectedUser_role' => 'required|in:admin,tutor,client',
        ]);
        $user = User::findOrFail($this->selectedUser->id);
        // Update the user
        $user->update([
            'name' => $this->selectedUser_name,
            'email' => $this->selectedUser_email,
            'role' => $this->selectedUser_role,
        ]);

        // Reset and provide feedback
        $this->showEditModal = false;
        $this->selectedUser = null;
        session()->flash('success', 'User updated successfully.');
    }

    public function create()
    {
        $this->reset(['name', 'email', 'role', 'password']);
        $this->showCreateModal = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,tutor,client',
            'password' => 'required|min:8',
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'password' => bcrypt($this->password),
        ]);

        try{
            Mail::to($user->email)->send(new UserCreatedEmail($user));
            session()->flash('success', 'User created successfully');
        } catch (\Exception $e) {;
            Log::error('Mail sending failed: ' . $e->getMessage());

            session()->flash('success', 'User created successfully (but notification was not sent). Please contact support team');
        }

        $this->showCreateModal = false;
        event(new Registered($user));
        session()->flash('success', 'User created successfully.');
    }

    public function openDelete($id)
    {
        $this->selectedUser = User::findOrFail($id);
        $this->confirmDelete = true;
    }

    public function deleteUser()
    {
        $deletedUserId = 0;

        $user = User::findOrFail($this->selectedUser->id);
        Booking::where('client_id', $user->id)->update(['client_id' => $deletedUserId]);
        $user->delete();

        $this->confirmDelete = false;
        $this->selectedUser = null;
        session()->flash('success', 'User deleted successfully.');
    }

    public function closeModals()
    {
        $this->showEditModal = false;
        $this->showCreateModal = false;
        $this->confirmDelete = false;
        $this->selectedUser = null;
    }   
}

