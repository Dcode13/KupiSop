<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Pengguna')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'kasir';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => $this->editingId
                ? 'nullable|string|min:8'
                : 'required|string|min:8',
            'role' => ['required', Rule::in(['admin', 'kasir', 'barista'])],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->role = 'kasir';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function edit(User $user): void
    {
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->getRoleNames()->first() ?? 'kasir';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        // Set / ubah password hanya jika diisi
        if ($this->password !== '') {
            $data['password'] = Hash::make($this->password);
        }

        $user = User::updateOrCreate(['id' => $this->editingId], $data);
        $user->syncRoles($this->role);

        $this->showModal = false;
        $this->dispatch('notify', message: $this->editingId ? 'Pengguna diperbarui.' : 'Pengguna ditambahkan.');
        $this->reset(['editingId', 'name', 'email', 'password']);
    }

    public function delete(User $user): void
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', message: 'Tidak bisa menghapus akun sendiri.', type: 'error');

            return;
        }

        $user->delete();
        $this->dispatch('notify', message: 'Pengguna dihapus.');
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, fn ($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => Role::pluck('name'),
        ]);
    }
}
