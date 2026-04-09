<?php

namespace App\Livewire\Admin;

use App\Models\Owner;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class Owners extends Component
{
  use WithPagination;

  public string $filterStatus = 'active';

  public string $filterPortal = '';

  public string $filterGarage = '';

  public string $filterStorage = '';

  public function updatedFilterStatus(): void
  {
    $this->resetPage();
  }

  public function updatedFilterPortal(): void
  {
    $this->resetPage();
  }

  public function updatedFilterGarage(): void
  {
    $this->resetPage();
  }

  public function updatedFilterStorage(): void
  {
    $this->resetPage();
  }

  public function render(): View
  {
    $query = Owner::with([
      'user',
      'activeAssignments.property.location',
      'assignments.property.location',
    ]);

    if ($this->filterStatus === 'active') {
      $query->whereHas('activeAssignments');
    } elseif ($this->filterStatus === 'inactive') {
      $query->whereDoesntHave('activeAssignments');
    }

    if ($this->filterPortal !== '') {
      $query->whereHas('activeAssignments.property.location', function (Builder $q) {
        $q->where('type', 'portal')->where('id', $this->filterPortal);
      });
    }

    if ($this->filterGarage !== '') {
      $query->whereHas('activeAssignments.property.location', function (Builder $q) {
        $q->where('type', 'garage')->where('id', $this->filterGarage);
      });
    }

    if ($this->filterStorage !== '') {
      $query->whereHas('activeAssignments.property.location', function (Builder $q) {
        $q->where('type', 'storage')->where('id', $this->filterStorage);
      });
    }

    $owners = $query->orderBy('coprop1_name')->paginate(20);

    $portals = Location::portals()->orderBy('code')->get();
    $garages = Location::garages()->orderBy('code')->get();
    $storages = Location::storage()->orderBy('code')->get();

    return view('livewire.admin.owners.index', [
      'owners' => $owners,
      'portals' => $portals,
      'garages' => $garages,
      'storages' => $storages,
    ]);
  }
}
