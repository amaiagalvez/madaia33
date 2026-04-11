<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Owner;
use Livewire\Component;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use App\Actions\Locations\AssignLocationChiefAction;
use Illuminate\Support\Facades\Auth;

class LocationDetail extends Component
{
    private AssignLocationChiefAction $assignLocationChiefAction;

    public Location $location;

    public string $chiefPropertyId = '';

    public string $newPropertyName = '';

    public string $newCommunityPct = '';

    public string $newLocationPct = '';

    public bool $showAddForm = false;

    public ?int $editingPropertyId = null;

    public string $editName = '';

    public string $editCommunityPct = '';

    public string $editLocationPct = '';

    public function boot(AssignLocationChiefAction $assignLocationChiefAction): void
    {
        $this->assignLocationChiefAction = $assignLocationChiefAction;
    }

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'newPropertyName' => 'required|string|max:20',
            'editName' => 'required|string|max:20',
            'editCommunityPct' => 'required|numeric|min:0|max:100',
            'editLocationPct' => 'required|numeric|min:0|max:100',
        ];
    }

    private function normalizeDecimalInput(string $value): string
    {
        return str_replace(',', '.', trim($value));
    }

    public function addProperty(): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $this->newCommunityPct = $this->normalizeDecimalInput($this->newCommunityPct);
        $this->newLocationPct = $this->normalizeDecimalInput($this->newLocationPct);

        $this->validate([
            'newPropertyName' => 'required|string|max:20',
            'newCommunityPct' => 'required|numeric|min:0|max:100',
            'newLocationPct' => 'required|numeric|min:0|max:100',
        ], [], [
            'newPropertyName' => __('admin.locations.property_name'),
            'newCommunityPct' => __('admin.locations.community_pct'),
            'newLocationPct' => __('admin.locations.location_pct'),
        ]);

        Property::create([
            'location_id' => $this->location->id,
            'name' => $this->newPropertyName,
            'community_pct' => $this->newCommunityPct,
            'location_pct' => $this->newLocationPct,
        ]);

        $this->newPropertyName = '';
        $this->newCommunityPct = '';
        $this->newLocationPct = '';
        $this->showAddForm = false;
    }

    public function startEditing(int $propertyId): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $property = Property::findOrFail($propertyId);

        $this->editingPropertyId = $propertyId;
        $this->editName = $property->name;
        $this->editCommunityPct = (string) ($property->community_pct ?? '');
        $this->editLocationPct = (string) ($property->location_pct ?? '');
    }

    public function saveProperty(): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $this->editCommunityPct = $this->normalizeDecimalInput($this->editCommunityPct);
        $this->editLocationPct = $this->normalizeDecimalInput($this->editLocationPct);

        $this->validate([
            'editName' => 'required|string|max:20',
            'editCommunityPct' => 'required|numeric|min:0|max:100',
            'editLocationPct' => 'required|numeric|min:0|max:100',
        ]);

        $property = Property::findOrFail($this->editingPropertyId);
        $property->update([
            'name' => $this->editName,
            'community_pct' => $this->editCommunityPct,
            'location_pct' => $this->editLocationPct,
        ]);

        $this->editingPropertyId = null;
    }

    public function cancelEditing(): void
    {
        $this->editingPropertyId = null;
    }

    public function openAddForm(): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $this->showAddForm = true;
        $this->newPropertyName = '';
        $this->newCommunityPct = '';
        $this->newLocationPct = '';
        $this->resetValidation([
            'newPropertyName',
            'newCommunityPct',
            'newLocationPct',
        ]);
    }

    public function cancelAddForm(): void
    {
        $this->showAddForm = false;
        $this->newPropertyName = '';
        $this->newCommunityPct = '';
        $this->newLocationPct = '';
        $this->resetValidation([
            'newPropertyName',
            'newCommunityPct',
            'newLocationPct',
        ]);
    }

    public function saveChiefOwner(): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        if (! $this->supportsChiefOwnerSelection()) {
            return;
        }

        $candidateIds = $this->chiefPropertiesQuery()
            ->pluck('id')
            ->all();

        $validated = $this->validate([
            'chiefPropertyId' => ['required', 'integer', Rule::in($candidateIds)],
        ], [], [
            'chiefPropertyId' => __('admin.locations.chief_property'),
        ]);

        $property = $this->chiefPropertiesQuery()
            ->with([
                'activeAssignments.owner',
            ])
            ->findOrFail((int) $validated['chiefPropertyId']);

        $activeAssignment = $property->activeAssignments->first();

        if ($activeAssignment === null) {
            $this->addError('chiefPropertyId', __('admin.locations.chief_owner_must_belong_to_location'));

            return;
        }

        $owner = $activeAssignment->owner;

        if (! $owner instanceof Owner || $owner->user_id === null) {
            $this->addError('chiefPropertyId', __('admin.locations.chief_owner_without_user'));

            return;
        }

        $this->assignLocationChiefAction->execute($this->location, $owner);
        $this->chiefPropertyId = (string) $property->id;
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $isChiefSelectable = $this->supportsChiefOwnerSelection();
        $currentChiefOwnerId = $isChiefSelectable ? $this->currentChiefOwnerId() : null;
        $chiefProperties = $isChiefSelectable
            ? $this->chiefPropertiesQuery()->get()
            : collect();

        if ($this->chiefPropertyId === '' && $currentChiefOwnerId !== null) {
            $currentChiefPropertyId = $this->currentChiefPropertyId($currentChiefOwnerId);

            if ($currentChiefPropertyId !== null) {
                $this->chiefPropertyId = (string) $currentChiefPropertyId;
            }
        }

        $properties = $this->location->properties()
            ->with(['activeAssignments'])
            ->withCount(['activeAssignments'])
            ->orderBy('name', 'asc')
            ->get();

        return view('livewire.admin.locations.detail', [
            'isChiefSelectable' => $isChiefSelectable,
            'currentChiefOwnerId' => $currentChiefOwnerId,
            'chiefProperties' => $chiefProperties,
            'properties' => $properties,
        ]);
    }

    private function supportsChiefOwnerSelection(): bool
    {
        return in_array($this->location->type, ['portal', 'garage'], true);
    }

    private function currentChiefOwnerId(): ?int
    {
        return Owner::query()
            ->whereHas('user.managedLocations', function ($query): void {
                $query->whereKey($this->location->id);
            })
            ->whereHas('user.roles', function ($query): void {
                $query->where('name', Role::COMMUNITY_ADMIN);
            })
            ->orderBy('owners.id')
            ->value('owners.id');
    }

    private function currentChiefPropertyId(int $currentChiefOwnerId): ?int
    {
        return Property::query()
            ->where('location_id', $this->location->id)
            ->whereHas('activeAssignments', function ($query) use ($currentChiefOwnerId): void {
                $query->where('owner_id', $currentChiefOwnerId);
            })
            ->orderBy('name')
            ->value('id');
    }

    /**
     * @return Builder<Property>
     */
    private function chiefPropertiesQuery(): Builder
    {
        return Property::query()
            ->where('location_id', $this->location->id)
            ->whereHas('activeAssignments.owner', function ($query): void {
                $query->whereNotNull('user_id');
            })
            ->with([
                'activeAssignments' => function ($query): void {
                    $query->with('owner')->orderBy('id');
                },
            ])
            ->orderBy('name');
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
