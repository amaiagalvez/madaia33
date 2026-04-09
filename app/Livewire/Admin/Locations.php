<?php

namespace App\Livewire\Admin;

use App\Models\Location;
use Livewire\Component;
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
    $locations = Location::where('type', $this->type)
      ->withCount(['properties'])
      ->orderBy('code')
      ->paginate(20);

    return view('livewire.admin.locations.index', [
      'locations' => $locations,
    ]);
  }
}
