<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Location;
use App\Models\Property;
use Illuminate\Contracts\View\View;

class LocationDetail extends Component
{
    public Location $location;

    public string $newPropertyName = '';

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
        $isStorage = $this->location->type === 'storage';

        return [
            'newPropertyName' => 'required|string|max:20',
            'editName' => 'required|string|max:20',
            'editCommunityPct' => $isStorage ? 'nullable' : 'required|numeric|min:0|max:100',
            'editLocationPct' => $isStorage ? 'nullable' : 'required|numeric|min:0|max:100',
        ];
    }

    public function addProperty(): void
    {
        $this->validateOnly('newPropertyName');

        Property::create([
            'location_id' => $this->location->id,
            'name' => $this->newPropertyName,
            'community_pct' => null,
            'location_pct' => null,
        ]);

        $this->newPropertyName = '';
        $this->showAddForm = false;
    }

    public function startEditing(int $propertyId): void
    {
        $property = Property::findOrFail($propertyId);

        $this->editingPropertyId = $propertyId;
        $this->editName = $property->name;
        $this->editCommunityPct = (string) ($property->community_pct ?? '');
        $this->editLocationPct = (string) ($property->location_pct ?? '');
    }

    public function saveProperty(): void
    {
        $this->validate([
            'editName' => 'required|string|max:20',
            'editCommunityPct' => $this->location->type === 'storage' ? 'nullable' : 'required|numeric|min:0|max:100',
            'editLocationPct' => $this->location->type === 'storage' ? 'nullable' : 'required|numeric|min:0|max:100',
        ]);

        $property = Property::findOrFail($this->editingPropertyId);
        $property->update([
            'name' => $this->editName,
            'community_pct' => $this->location->type === 'storage' ? null : $this->editCommunityPct,
            'location_pct' => $this->location->type === 'storage' ? null : $this->editLocationPct,
        ]);

        $this->editingPropertyId = null;
    }

    public function cancelEditing(): void
    {
        $this->editingPropertyId = null;
    }

    public function render(): View
    {
        $properties = $this->location->properties()
            ->with(['activeAssignments'])
            ->withCount(['activeAssignments'])
            ->orderBy('name')
            ->get();

        return view('livewire.admin.locations.detail', [
            'properties' => $properties,
        ]);
    }
}
