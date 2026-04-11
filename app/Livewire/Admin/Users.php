<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Location;
use App\SupportedLocales;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class Users extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $isActive = true;

    /**
     * @var array<int, string>
     */
    public array $selectedRoles = [];

    /**
     * @var array<int, string>
     */
    public array $selectedManagedLocations = [];

    public ?int $confirmingDeleteUserId = null;

    public string $search = '';

    public function mount(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createUser(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editUser(int $userId): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        $user = User::query()
            ->with(['roles', 'managedLocations'])
            ->findOrFail($userId);

        abort_if($user->id === 1, 403);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->isActive = (bool) $user->is_active;
        $this->selectedRoles = $user->roleNames()->all();
        $this->selectedManagedLocations = $user->managedLocations()->pluck('locations.id')->map(static fn($id): string => (string) $id)->all();
        $this->showForm = true;
    }

    public function saveUser(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        if ($this->editingUserId !== null) {
            abort_if($this->editingUserId === 1, 403);
        }

        $validated = $this->validate($this->rules(), [], [
            'name' => __('validation.attributes.name'),
            'email' => __('validation.attributes.email'),
            'password' => __('validation.attributes.password'),
            'selectedRoles' => __('admin.users.roles'),
            'selectedManagedLocations' => __('admin.users.managed_locations'),
        ]);

        $user = $this->editingUserId !== null
            ? User::query()->findOrFail($this->editingUserId)
            : new User;

        abort_if($user->id === 1, 403);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->is_active = (bool) $validated['isActive'];

        if ($validated['password'] !== '') {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncOwnerIdentity();

        $this->syncUserRolesAndLocations($user);

        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    public function confirmDelete(int $userId): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);
        abort_if($userId === 1, 403);

        $this->confirmingDeleteUserId = $userId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteUserId = null;
    }

    public function deleteUser(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        if ($this->confirmingDeleteUserId === null) {
            return;
        }

        abort_if($this->confirmingDeleteUserId === 1, 403);

        $user = User::query()->findOrFail($this->confirmingDeleteUserId);
        $user->delete();

        $this->confirmingDeleteUserId = null;
        $this->resetPage();
    }

    public function loginAs(int $userId): void
    {
        abort_unless($this->currentUser()?->isSuperadmin(), 403);
        abort_if($userId === 1, 403);

        $user = User::query()->where('id', '!=', 1)->findOrFail($userId);

        Auth::login($user);
        session()->regenerate();

        $this->redirect(route(SupportedLocales::routeName('home')));
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * @return array<string, array<int, Rule|string>>
     */
    private function rules(): array
    {
        $emailRule = Rule::unique('users', 'email');

        if ($this->editingUserId !== null) {
            $emailRule = $emailRule->ignore($this->editingUserId);
        }

        $roleRule = Rule::in(array_values(array_filter(Role::names(), static fn(string $name): bool => $name !== Role::SUPER_ADMIN)));

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'password' => ['nullable', 'string', 'min:8'],
            'isActive' => ['boolean'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', $roleRule],
            'selectedManagedLocations' => ['array'],
            'selectedManagedLocations.*' => ['integer', 'exists:locations,id'],
        ];

        if ($this->editingUserId === null) {
            $rules['password'] = ['required', 'string', 'min:8'];
        }

        if (in_array(Role::COMMUNITY_ADMIN, $this->selectedRoles, true)) {
            $rules['selectedManagedLocations'] = ['required', 'array', 'min:1'];
        }

        return $rules;
    }

    private function syncUserRolesAndLocations(User $user): void
    {
        $roleNames = collect($this->selectedRoles)
            ->filter(static fn(string $role): bool => $role !== Role::SUPER_ADMIN)
            ->unique()
            ->values()
            ->all();

        $user->syncRoleNames($roleNames);

        if (! in_array(Role::COMMUNITY_ADMIN, $roleNames, true)) {
            $user->managedLocations()->sync([]);

            return;
        }

        $locationIds = collect($this->selectedManagedLocations)
            ->map(static fn(string $locationId): int => (int) $locationId)
            ->unique()
            ->values()
            ->all();

        $user->managedLocations()->sync($locationIds);
    }

    private function resetForm(): void
    {
        $this->resetValidation();

        $this->editingUserId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->isActive = true;
        $this->selectedRoles = [];
        $this->selectedManagedLocations = [];
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        $users = User::query()
            ->with(['roles'])
            ->where('id', '!=', 1)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => collect(Role::names())
                ->reject(static fn(string $name): bool => $name === Role::SUPER_ADMIN)
                ->values()
                ->all(),
            'communityLocations' => Location::query()
                ->whereIn('type', ['portal', 'local', 'garage'])
                ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 WHEN type = 'garage' THEN 3 ELSE 4 END")
                ->orderBy('code')
                ->get(['id', 'code', 'type']),
        ]);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
