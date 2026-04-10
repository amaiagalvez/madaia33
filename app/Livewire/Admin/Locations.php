<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;

class Locations extends Component
{
    use WithPagination;

    public string $type = 'portal';

    /** @var string[] */
    public array $types = ['portal', 'garage', 'storage'];

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->resetPage();
    }

    public function render(): View
    {
        abort_unless(auth()->user()?->canAccessAdminPanel(), 403);

        $query = Location::where('type', $this->type);

        if (! auth()->user()?->canManageAllLocations()) {
            $query->whereIn('id', auth()->user()?->managedLocations()->pluck('locations.id'));
        }

        $locations = $query
            ->withCount(['properties'])
            ->orderBy('code')
            ->paginate(20);

        return view('livewire.admin.locations.index', [
            'locations' => $locations,
        ]);
    }
}
