<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Models\Notice;
use Livewire\Component;
use App\Models\Location;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\NoticeLocation;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Concerns\BuildsLocaleFieldConfigs;

class AdminNoticeManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithPagination;

    // Form state
    public ?int $editingId = null;

    public string $titleEu = '';

    public string $titleEs = '';

    public string $contentEu = '';

    public string $contentEs = '';

    public bool $isPublic = false;

    /** @var string[] */
    public array $selectedLocations = [];

    // Delete confirmation
    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    public ?int $confirmingPublishId = null;

    public string $publishAction = '';

    public bool $showPublishModal = false;

    public string $sortColumn = 'published_at';

    public string $sortDir = 'desc';

    // UI state
    public bool $showForm = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $user = $this->currentUser();

        abort_unless($user?->canManageNotices(), 403);

        $selectedLocationRules = ['string'];

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $selectedLocationRules[] = Rule::in($this->allowedLocationCodes());
        }

        $selectedLocationsRule = ['array'];

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $selectedLocationsRule = ['required', 'array', 'min:1'];
        }

        return [
            'titleEu' => 'required|string|max:255',
            'titleEs' => 'nullable|string|max:255',
            'contentEu' => 'required|string',
            'contentEs' => 'nullable|string',
            'isPublic' => 'boolean',
            'selectedLocations' => $selectedLocationsRule,
            'selectedLocations.*' => $selectedLocationRules,
        ];
    }

    public function createNotice(): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $this->resetForm();
        $this->showForm = true;
    }

    public function editNotice(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $notice = Notice::with(['locations.location'])->findOrFail($id);

        $this->editingId = $notice->id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedLocations = $notice->locations
            ->map(fn(NoticeLocation $location): ?string => $location->location_code)
            ->filter()
            ->values()
            ->all();

        $user = $this->currentUser();

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            $this->selectedLocations = [];
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $allowedLocationCodes = $this->allowedLocationCodes();

            $this->selectedLocations = collect($this->selectedLocations)
                ->filter(static fn(string $code): bool => in_array($code, $allowedLocationCodes, true))
                ->values()
                ->all();
        }

        $this->showForm = true;
        $this->dispatch('admin-notice-form-focus');
    }

    public function saveNotice(): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $user = $this->currentUser();

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            $this->selectedLocations = [];
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $allowedLocationCodes = $this->allowedLocationCodes();

            $this->selectedLocations = collect($this->selectedLocations)
                ->filter(static fn(string $code): bool => in_array($code, $allowedLocationCodes, true))
                ->values()
                ->all();
        }

        $this->validate();

        $notice = $this->upsertNotice();

        $this->syncLocations($notice);

        $this->resetForm();
        $this->showForm = false;
        session()->flash('message', __('general.messages.saved'));
        $this->dispatch('admin-notice-saved');
    }

    private function upsertNotice(): Notice
    {
        if ($this->editingId !== null) {
            $notice = Notice::findOrFail($this->editingId);
            $notice->update($this->noticePayload($notice));

            return $notice;
        }

        return Notice::create($this->noticePayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function noticePayload(?Notice $existingNotice = null): array
    {
        $slug = Str::slug($this->titleEu);
        $resolvedSlug = $slug;

        if ($resolvedSlug === '') {
            $resolvedSlug = $existingNotice !== null ? $existingNotice->slug : (string) Str::uuid();
        }

        return [
            'slug' => $resolvedSlug,
            'title_eu' => $this->titleEu,
            'title_es' => $this->titleEs ?: null,
            'content_eu' => $this->contentEu,
            'content_es' => $this->contentEs ?: null,
            'is_public' => $this->isPublic,
            'published_at' => $this->resolvePublishedAt($existingNotice),
        ];
    }

    private function resolvePublishedAt(?Notice $existingNotice = null): mixed
    {
        if ($existingNotice !== null) {
            if (! $this->isPublic) {
                return $existingNotice->published_at;
            }

            return $existingNotice->published_at ?: now();
        }

        return $this->isPublic ? now() : null;
    }

    public function publishNotice(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $notice = Notice::findOrFail($id);
        $notice->update([
            'is_public' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublishNotice(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $notice = Notice::findOrFail($id);
        $notice->update(['is_public' => false]);
    }

    public function confirmPublish(int $id, bool $publish): void
    {
        $this->confirmingPublishId = $id;
        $this->publishAction = $publish ? 'publish' : 'unpublish';
        $this->showPublishModal = true;
    }

    public function doPublish(): void
    {
        if ($this->confirmingPublishId === null) {
            return;
        }

        if ($this->publishAction === 'publish') {
            $this->publishNotice($this->confirmingPublishId);
        } else {
            $this->unpublishNotice($this->confirmingPublishId);
        }

        $this->cancelPublish();
    }

    public function cancelPublish(): void
    {
        $this->confirmingPublishId = null;
        $this->publishAction = '';
        $this->showPublishModal = false;
    }

    public function confirmDelete(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $this->confirmingDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function deleteNotice(): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        if ($this->confirmingDeleteId) {
            Notice::findOrFail($this->confirmingDeleteId)->delete();
            $this->confirmingDeleteId = null;
            $this->showDeleteModal = false;
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function sortBy(string $column): void
    {
        if ($this->sortColumn === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDir = 'desc';
        }

        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->titleEu = '';
        $this->titleEs = '';
        $this->contentEu = '';
        $this->contentEs = '';
        $this->isPublic = false;
        $this->selectedLocations = [];
        $this->resetValidation();
    }

    /**
     * Sync the notice_locations pivot for the given notice.
     */
    private function syncLocations(Notice $notice): void
    {
        $notice->locations()->delete();

        $rows = [];

        foreach (array_values(array_unique($this->selectedLocations)) as $selected) {
            $locationId = $this->resolveSelectionToForeignKey((string) $selected);

            if ($locationId === null) {
                continue;
            }

            $rows[] = [
                'notice_id' => $notice->id,
                'location_id' => $locationId,
            ];
        }

        if ($rows !== []) {
            NoticeLocation::insert($rows);
        }
    }

    private function resolveSelectionToForeignKey(string $selected): ?int
    {
        return Location::query()
            ->where('code', $selected)
            ->value('id');
    }

    /**
     * @return array<int, array{code: string, type: string, label: string}>
     */
    private function allLocationOptions(): array
    {
        $user = $this->currentUser();

        $query = Location::query()->whereIn('type', ['portal', 'local', 'garage']);

        if ($user?->hasRole(Role::GENERAL_ADMIN)) {
            return [];
        }

        if ($user?->hasRole(Role::COMMUNITY_ADMIN)) {
            $query->whereIn('id', $user->managedLocations()->pluck('locations.id'));
        }

        $locations = $query
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'local' THEN 2 WHEN type = 'garage' THEN 3 ELSE 4 END")
            ->orderBy('code')
            ->get();

        return $locations
            ->map(fn(Location $location): array => [
                'code' => $location->code,
                'type' => $location->type,
                'label' => $this->locationLabel($location) . $location->code,
            ])
            ->all();
    }

    private function locationLabel(Location $location): string
    {
        return match ($location->type) {
            'portal' => __('admin.locations.types.portal') . ' ',
            'local' => __('admin.locations.types.local') . ' ',
            'garage' => __('admin.locations.types.garage') . ' ',
            default => '',
        };
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $allowedSortColumns = ['created_at', 'is_public', 'published_at'];
        $sortColumn = in_array($this->sortColumn, $allowedSortColumns, true) ? $this->sortColumn : 'published_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc'], true) ? $this->sortDir : 'desc';

        $notices = Notice::with(['locations.location'])
            ->orderBy($sortColumn, $sortDir)
            ->paginate(12);

        return view('livewire.admin.notice-manager', [
            'notices' => $notices,
            'allLocations' => $this->allLocationOptions(),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function allowedLocationCodes(): array
    {
        return collect($this->allLocationOptions())
            ->map(static fn(array $location): string => $location['code'])
            ->values()
            ->all();
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
