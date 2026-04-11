<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class LocationDetail extends Component
{
    public Location $location;

    public string $newPropertyName = '';

    public string $newCommunityPct = '';

    public string $newLocationPct = '';

    public bool $showAddForm = false;

    public ?int $editingPropertyId = null;

    public string $editName = '';

    public string $editCommunityPct = '';

    public string $editLocationPct = '';

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

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageLocation($this->location), 403);

        $properties = $this->location->properties()
            ->with(['activeAssignments'])
            ->withCount(['activeAssignments'])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.locations.detail', [
            'properties' => $properties,
        ]);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
