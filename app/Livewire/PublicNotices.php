<?php

namespace App\Livewire;

use App\Models\Notice;
use Livewire\Component;
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
            ->with('locations')
            ->when($this->locationFilter !== '', function ($query) {
                $query->where(function ($q) {
                    // Include notices with the selected location
                    $q->whereHas('locations', fn ($l) => $l->where('location_code', $this->locationFilter));
                    // Also include general notices (no locations)
                    $q->orWhereDoesntHave('locations');
                });
            })
            ->orderByDesc('published_at')
            ->paginate(9);
    }

    public function render(): View
    {
        return view('livewire.front.public-notices', [
            'notices' => $this->getNoticesProperty(),
        ]);
    }
}
