<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use Livewire\Component;
use App\Models\Location;
use App\SupportedLocales;
use Illuminate\Support\Str;
use App\Models\VotingBallot;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\UserLoginSession;
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

    public ?int $confirmingResetPasswordUserId = null;

    public bool $showResetPasswordModal = false;

    public string $search = '';

    public string $roleFilter = 'all';

    public ?int $editingOwnerId = null;

    /**
     * @var array<int, array<string, string|null>>
     */
    public array $editingUserSessions = [];

    public function mount(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
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
            ->with(['roles', 'managedLocations', 'owner'])
            ->findOrFail($userId);

        abort_if($user->id === 1, 403);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->editingOwnerId = $user->owner?->id;
        $this->isActive = (bool) $user->is_active;
        $this->selectedRoles = $user->roleNames()->all();
        $this->selectedManagedLocations = $user->managedLocations()->pluck('locations.id')->map(static fn($id): string => (string) $id)->all();
        $this->loadEditingUserSessions($user->id);
        $this->showForm = true;
    }

    public function saveUser(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        if ($this->editingUserId !== null) {
            abort_if($this->editingUserId === 1, 403);
        }

        $validated = $this->validate($this->rules(), [], [
            'name' => __('admin.users.name'),
            'email' => __('admin.users.email'),
            'selectedRoles' => __('admin.users.roles'),
            'selectedManagedLocations' => __('admin.users.managed_locations'),
        ]);

        $user = $this->editingUserId !== null
            ? User::query()->findOrFail($this->editingUserId)
            : new User;

        abort_if($user->id === 1, 403);

        if ($this->editingUserId === null) {
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = Str::password(20);
        }

        $user->is_active = (bool) $validated['isActive'];

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

    public function confirmResetPassword(int $userId): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);
        abort_if($userId === 1, 403);

        $this->confirmingResetPasswordUserId = $userId;
        $this->showResetPasswordModal = true;
    }

    public function cancelResetPassword(): void
    {
        $this->confirmingResetPasswordUserId = null;
        $this->showResetPasswordModal = false;
    }

    public function resetUserPassword(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        if ($this->confirmingResetPasswordUserId === null) {
            return;
        }

        abort_if($this->confirmingResetPasswordUserId === 1, 403);

        $user = User::query()->findOrFail($this->confirmingResetPasswordUserId);
        $user->password = '123456789';
        $user->save();

        $this->cancelResetPassword();

        session()->flash('message', __('admin.users.password_reset_success'));
    }

    public function deleteUser(): void
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        if ($this->confirmingDeleteUserId === null) {
            return;
        }

        abort_if($this->confirmingDeleteUserId === 1, 403);

        $user = User::query()->with('owner')->findOrFail($this->confirmingDeleteUserId);

        if (! $this->canDeleteUser($user)) {
            $this->confirmingDeleteUserId = null;

            return;
        }

        $user->delete();

        $this->confirmingDeleteUserId = null;
        $this->resetPage();
    }

    private function canDeleteUser(User $user): bool
    {
        $owner = $user->owner;

        if (! $owner instanceof Owner) {
            return true;
        }

        if (VotingBallot::query()->where('owner_id', $owner->id)->exists()) {
            session()->flash('error', __('admin.users.delete_blocked_has_votes'));

            return false;
        }

        if ($owner->assignments()->exists()) {
            session()->flash('error', __('admin.users.delete_blocked_has_assignments'));

            return false;
        }

        return true;
    }

    public function loginAs(int $userId): void
    {
        abort_unless($this->currentUser()?->isSuperadmin(), 403);
        abort_if($userId === 1, 403);

        $user = User::query()->where('id', '!=', 1)->findOrFail($userId);

        if (! session()->has('impersonator_user_id')) {
            session()->put('impersonator_user_id', $this->currentUser()->id);
        }

        Auth::login($user);

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
            'isActive' => ['boolean'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['string', $roleRule],
            'selectedManagedLocations' => ['array'],
            'selectedManagedLocations.*' => ['integer', 'exists:locations,id'],
        ];

        if ($this->editingUserId !== null) {
            $rules['name'] = ['nullable', 'string', 'max:255'];
            $rules['email'] = ['nullable', 'email', 'max:255', $emailRule];
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
        $this->editingOwnerId = null;
        $this->editingUserSessions = [];
        $this->password = '';
        $this->isActive = true;
        $this->selectedRoles = [];
        $this->selectedManagedLocations = [];
    }

    private function loadEditingUserSessions(int $userId): void
    {
        $this->editingUserSessions = UserLoginSession::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_in_at')
            ->get(['id', 'ip_address', 'logged_in_at', 'logged_out_at'])
            ->map(static function (UserLoginSession $session): array {
                return [
                    'id' => (string) $session->id,
                    'ip_address' => $session->ip_address,
                    'logged_in_at' => $session->logged_in_at,
                    'logged_out_at' => $session->logged_out_at,
                ];
            })
            ->all();
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageUsers(), 403);

        $users = User::query()
            ->with(['roles', 'managedLocations'])
            ->where('id', '!=', 1)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter !== 'all', function ($query): void {
                $query->whereHas('roles', function ($rolesQuery): void {
                    $rolesQuery->where('name', $this->roleFilter);
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
            'roleOptions' => collect(Role::names())
                ->reject(static fn(string $name): bool => $name === Role::SUPER_ADMIN)
                ->map(static fn(string $name): array => [
                    'value' => $name,
                    'label' => __('admin.users.roles_labels.' . $name),
                ])
                ->values()
                ->all(),
            'communityLocationOptions' => Location::query()
                ->whereIn('type', ['portal', 'local', 'garage'])
                ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 WHEN type = 'garage' THEN 3 ELSE 4 END")
                ->orderBy('code')
                ->get(['id', 'code', 'type'])
                ->map(static fn(Location $location): array => [
                    'id' => (string) $location->id,
                    'label' => __('admin.locations.types.' . $location->type) . ' ' . $location->code,
                ])
                ->values()
                ->all(),
        ]);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
