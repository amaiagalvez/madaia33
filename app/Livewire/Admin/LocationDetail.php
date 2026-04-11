<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Owner;
use Livewire\Component;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use App\Actions\Locations\AssignLocationChiefAction;
use Illuminate\Support\Facades\Auth;

class LocationDetail extends Component
{
    private AssignLocationChiefAction $assignLocationChiefAction;

    public Location $location;

    public string $chiefOwnerId = '';

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

    public function saveChiefOwner(): void
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        if (! $this->supportsChiefOwnerSelection()) {
            return;
        }

        $candidateIds = $this->chiefCandidatesQuery()
            ->pluck('owners.id')
            ->all();

        $validated = $this->validate([
            'chiefOwnerId' => ['required', 'integer', Rule::in($candidateIds)],
        ], [], [
            'chiefOwnerId' => __('admin.locations.chief_owner'),
        ]);

        $owner = Owner::query()->findOrFail((int) $validated['chiefOwnerId']);

        $this->assignLocationChiefAction->execute($this->location, $owner);
        $this->chiefOwnerId = (string) $owner->id;
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $isChiefSelectable = $this->supportsChiefOwnerSelection();
        $currentChiefOwnerId = $isChiefSelectable ? $this->currentChiefOwnerId() : null;
        $chiefCandidates = $isChiefSelectable
            ? $this->chiefCandidatesQuery()->get()
            : collect();

        if ($this->chiefOwnerId === '' && $currentChiefOwnerId !== null) {
            $this->chiefOwnerId = (string) $currentChiefOwnerId;
        }

        $properties = $this->location->properties()
            ->with(['activeAssignments'])
            ->withCount(['activeAssignments'])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.locations.detail', [
            'isChiefSelectable' => $isChiefSelectable,
            'currentChiefOwnerId' => $currentChiefOwnerId,
            'chiefCandidates' => $chiefCandidates,
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

    private function chiefCandidatesQuery()
    {
        return Owner::query()
            ->whereNotNull('user_id')
            ->whereHas('activeAssignments.property', function ($query): void {
                $query->where('location_id', $this->location->id);
            })
            ->orderBy('coprop1_name');
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
