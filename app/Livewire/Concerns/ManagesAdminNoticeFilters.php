<?php

namespace App\Livewire\Concerns;

use App\Models\Role;
use App\Models\User;
use App\Models\Notice;
use App\Models\NoticeTag;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

trait ManagesAdminNoticeFilters
{
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAttachments(): void
    {
        if ($this->editingId === null || $this->attachments === []) {
            return;
        }

        $this->uploadDocument($this->editingId);
    }

    public function removePendingAttachment(int $index): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        if (! array_key_exists($index, $this->attachments)) {
            return;
        }

        unset($this->attachments[$index]);
        $this->attachments = array_values($this->attachments);
    }

    public function setTagFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'untagged'], true)) {
            $this->tagFilter = $filter;
            $this->resetPage();

            return;
        }

        if (! ctype_digit($filter)) {
            return;
        }

        $allowedTagIds = $this->availableNoticeTags()
            ->pluck('id')
            ->map(static fn(int $id): string => (string) $id)
            ->all();

        if (! in_array($filter, $allowedTagIds, true)) {
            return;
        }

        $this->tagFilter = $filter;
        $this->resetPage();
    }

    /**
     * @return Collection<int, NoticeTag>
     */
    private function availableNoticeTags(): Collection
    {
        $user = $this->currentUser();

        if ($user?->hasRole(Role::CONSTRUCTION_MANAGER)) {
            $slugs = $user->constructions()
                ->pluck('constructions.slug')
                ->all();

            if ($slugs === []) {
                return collect();
            }

            return NoticeTag::query()
                ->whereIn('slug', $slugs)
                ->orderBy('name_eu')
                ->get();
        }

        return NoticeTag::query()
            ->orderBy('name_eu')
            ->get();
    }

    /**
     * @return Builder<Notice>
     */
    private function noticesQuery(User $user): Builder
    {
        $query = Notice::with([
            'locations.location',
            'tag.construction',
            'documents' => fn($documentQuery) => $documentQuery->withCount('downloads'),
        ]);

        $this->applySearchFilter($query);
        $this->applyTagFilter($query);
        $this->applyRoleVisibilityFilter($query, $user);

        return $query->orderBy($this->resolvedSortColumn(), $this->resolvedSortDirection());
    }

    /**
     * @param  Builder<Notice>  $query
     */
    private function applySearchFilter(Builder $query): void
    {
        if (trim($this->search) === '') {
            return;
        }

        $term = '%' . trim($this->search) . '%';

        $query->where(function (Builder $innerQuery) use ($term): void {
            $innerQuery
                ->where('title_eu', 'like', $term)
                ->orWhere('title_es', 'like', $term)
                ->orWhere('content_eu', 'like', $term)
                ->orWhere('content_es', 'like', $term);
        });
    }

    /**
     * @param  Builder<Notice>  $query
     */
    private function applyTagFilter(Builder $query): void
    {
        if ($this->tagFilter === 'untagged') {
            $query->whereNull('notice_tag_id');

            return;
        }

        if ($this->tagFilter !== 'all' && ctype_digit($this->tagFilter)) {
            $query->where('notice_tag_id', (int) $this->tagFilter);
        }
    }

    /**
     * @param  Builder<Notice>  $query
     */
    private function applyRoleVisibilityFilter(Builder $query, User $user): void
    {
        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            $query->whereDoesntHave('locations');

            return;
        }

        if (! $user->hasRole(Role::COMMUNITY_ADMIN)) {
            return;
        }

        $managedLocationIds = $user->managedLocations()->pluck('locations.id')->all();

        if ($managedLocationIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereHas('locations', function (Builder $locationsQuery) use ($managedLocationIds): void {
            $locationsQuery->whereIn('location_id', $managedLocationIds);
        });
    }

    private function resolvedSortColumn(): string
    {
        $allowedSortColumns = ['created_at', 'is_public', 'published_at'];

        return in_array($this->sortColumn, $allowedSortColumns, true)
            ? $this->sortColumn
            : 'published_at';
    }

    private function resolvedSortDirection(): string
    {
        return in_array($this->sortDir, ['asc', 'desc'], true)
            ? $this->sortDir
            : 'desc';
    }
}
