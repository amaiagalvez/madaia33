<?php

namespace App\Livewire;

use App\Models\Notice;
use Livewire\Component;
use App\Models\Location;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicNotices extends Component
{
    use WithPagination;

    public string $locationFilter = '';

    /**
     * Reset pagination when the location filter changes.
     */
    public function updatedLocationFilter(): void
    {
        $this->resetPage();
    }

    public function setLocationFilter(string $location): void
    {
        $this->locationFilter = $location;
        $this->resetPage();
    }

    /**
     * Determine whether a notice has a translation in the active locale.
     */
    public function hasTranslation(Notice $notice): bool
    {
        $locale = App::getLocale();
        $title = $notice->{"title_{$locale}"};
        $content = $notice->{"content_{$locale}"};

        return filled($title) || filled($content);
    }

    /**
     * @return LengthAwarePaginator<int, Notice>
     */
    public function getNoticesProperty(): LengthAwarePaginator
    {
        return Notice::public()
            ->whereNull('notice_tag_id')
            ->with(['locations.location'])
            ->when($this->locationFilter !== '', function ($query) {
                $query->where(function ($q) {
                    // Include notices with the selected location
                    $q->whereHas('locations', function ($locationQuery): void {
                        $locationQuery->whereHas('location', fn ($query) => $query->where('id', $this->locationFilter));
                    });
                    // Also include general notices (no locations)
                    $q->orWhereDoesntHave('locations');
                });
            })
            ->orderByDesc('published_at')
            ->paginate(9);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function availableFilterLocations(): array
    {
        return Location::query()
            ->whereIn('type', ['portal', 'local', 'garage'])
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 WHEN type = 'garage' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get(['id', 'type', 'name'])
            ->map(fn (Location $location): array => [
                'id' => $location->id,
                'label' => match ($location->type) {
                    'portal' => __('notices.portal') . ' ' . $location->name,
                    'local' => __('notices.local') . ' ' . $location->name,
                    default => __('notices.garage') . ' ' . $location->name,
                },
            ])
            ->all();
    }

    public function render(): View
    {
        return view('livewire.front.public-notices', [
            'notices' => $this->getNoticesProperty(),
            'filterLocations' => $this->availableFilterLocations(),
        ]);
    }
}
