<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class Locations extends Component
{
    use WithPagination;

    public string $type = 'portal';

    /** @var string[] */
    public array $types = ['portal', 'local', 'garage', 'storage'];

    public function setType(string $type): void
    {
        $this->type = $type;
        $this->resetPage();
    }

    public function render(): View
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canAccessAdminPanel(), 403);

        $query = Location::where('type', $this->type);

        if (! $user->canManageAllLocations()) {
            $query->whereIn('id', $user->managedLocations()->pluck('locations.id'));
        }

        $locations = $query
            ->withCount(['properties'])
            ->withSum('properties', 'community_pct')
            ->withSum('properties', 'location_pct')
            ->orderBy('code')
            ->paginate(20);

        return view('livewire.admin.locations.index', [
            'locations' => $locations,
        ]);
    }
}
