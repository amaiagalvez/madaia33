<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class Locations extends Component
{
    use WithPagination;

    public string $type = 'portal';

    /** @var string[] */
    public array $types = ['portal', 'local', 'garage', 'storage'];

    public ?int $editingLocationId = null;

    public string $editCode = '';

    public string $editName = '';

    public bool $showEditForm = false;

    public bool $showCreateForm = false;

    public string $newCode = '';

    public string $newName = '';

    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->resetPage();
    }

    public function openEditForm(int $locationId): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $query = Location::query();

        if (! $this->canManageAllLocations($user)) {
            $query->whereIn('id', $this->managedLocationIds($user));
        }

        $location = $query->findOrFail($locationId);

        $this->showCreateForm = false;
        $this->editingLocationId = $location->id;
        $this->editCode = $location->code;
        $this->editName = $location->name;
        $this->showEditForm = true;
    }

    public function createLocation(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $this->cancelEditForm();
        $this->showCreateForm = true;
        $this->newCode = '';
        $this->newName = '';
        $this->resetValidation();
    }

    public function saveCreateForm(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $validated = $this->validate([
            'newCode' => ['required', 'string', 'max:10', Rule::unique('locations', 'code')],
            'newName' => ['required', 'string', 'max:50'],
        ]);

        Location::query()->create([
            'type' => $this->type,
            'code' => $validated['newCode'],
            'name' => $validated['newName'],
        ]);

        $this->cancelCreateForm();
        session()->flash('message', __('general.messages.saved'));
    }

    public function saveEditForm(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);
        abort_unless($this->editingLocationId !== null, 404);

        $validated = $this->validate([
            'editCode' => ['required', 'string', 'max:10', Rule::unique('locations', 'code')->ignore($this->editingLocationId)],
            'editName' => ['required', 'string', 'max:50'],
        ]);

        Location::query()->findOrFail($this->editingLocationId)->update([
            'code' => $validated['editCode'],
            'name' => $validated['editName'],
        ]);

        $this->cancelEditForm();
    }

    public function cancelEditForm(): void
    {
        $this->editingLocationId = null;
        $this->editCode = '';
        $this->editName = '';
        $this->showEditForm = false;
        $this->resetValidation();
    }

    public function cancelCreateForm(): void
    {
        $this->showCreateForm = false;
        $this->newCode = '';
        $this->newName = '';
        $this->resetValidation();
    }

    public function confirmDelete(int $locationId): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $query = Location::query();

        if (! $this->canManageAllLocations($user)) {
            $query->whereIn('id', $this->managedLocationIds($user));
        }

        $location = $query->findOrFail($locationId);

        $this->confirmingDeleteId = $location->id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function deleteLocation(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        if ($this->confirmingDeleteId === null) {
            return;
        }

        $query = Location::query();

        if (! $this->canManageAllLocations($user)) {
            $query->whereIn('id', $this->managedLocationIds($user));
        }

        $location = $query->findOrFail($this->confirmingDeleteId);

        if ($location->properties()->exists()) {
            $this->cancelDelete();
            session()->flash('error_message', __('admin.locations.delete_blocked_has_properties'));

            return;
        }

        $location->delete();
        $this->cancelDelete();
        session()->flash('message', __('general.messages.deleted'));
    }

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $query = Location::where('type', $this->type);

        if (! $this->canManageAllLocations($user)) {
            $query->whereIn('id', $this->managedLocationIds($user));
        }

        $locations = $query
            ->withCount(['properties'])
            ->withSum('properties', 'community_pct')
            ->withSum('properties', 'location_pct')
            ->orderBy('code')
            ->orderBy('name')
            ->paginate(20);

        $this->attachChiefPropertyNames($locations);

        return view('livewire.admin.locations.index', [
            'locations' => $locations,
        ]);
    }

    /**
     * @param  LengthAwarePaginator<int, Location>  $locations
     */
    private function attachChiefPropertyNames(LengthAwarePaginator $locations): void
    {
        $locationIds = $locations->getCollection()->pluck('id')->all();

        if ($locationIds === []) {
            return;
        }

        $chiefDataByLocation = DB::table('property_assignments')
            ->join('properties', 'properties.id', '=', 'property_assignments.property_id')
            ->join('owners', 'owners.id', '=', 'property_assignments.owner_id')
            ->join('users', 'users.id', '=', 'owners.user_id')
            ->join('location_user', function ($join): void {
                $join->on('location_user.user_id', '=', 'users.id')
                    ->on('location_user.location_id', '=', 'properties.location_id');
            })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.name', Role::COMMUNITY_ADMIN)
            ->whereNull('property_assignments.end_date')
            ->whereIn('properties.location_id', $locationIds)
            ->selectRaw('properties.location_id as location_id, MIN(properties.name) as chief_property_name, MIN(owners.coprop1_name) as community_admin_name')
            ->groupBy('properties.location_id')
            ->get()
            ->mapWithKeys(static function (object $row): array {
                return [
                    (int) $row->location_id => [
                        'chief_property_name' => $row->chief_property_name,
                        'community_admin_name' => $row->community_admin_name,
                    ],
                ];
            });

        $locations->setCollection(
            $locations->getCollection()->map(function (Location $location) use ($chiefDataByLocation): Location {
                $chiefData = $chiefDataByLocation->get($location->id);

                $location->setAttribute('chief_property_name', $chiefData['chief_property_name'] ?? null);
                $location->setAttribute('community_admin_name', $chiefData['community_admin_name'] ?? null);

                return $location;
            })
        );
    }

    private function canManageAllLocations(User $user): bool
    {
        return $user->canManageAllLocations();
    }

    /**
     * @return array<int, int>
     */
    private function managedLocationIds(User $user): array
    {
        return $user->managedLocations()->pluck('locations.id')->all();
    }
}
