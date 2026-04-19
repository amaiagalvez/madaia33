<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Models\Notice;
use Livewire\Component;
use App\Models\NoticeTag;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\NoticeLocation;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Concerns\HandlesNoticeDocuments;
use App\Concerns\ManagesNoticeLocations;
use App\Concerns\BuildsLocaleFieldConfigs;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class AdminNoticeManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use HandlesNoticeDocuments;
    use ManagesNoticeLocations;
    use WithFileUploads;
    use WithPagination;

    // Form state
    public ?int $editingId = null;

    public string $titleEu = '';

    public string $titleEs = '';

    public string $contentEu = '';

    public string $contentEs = '';

    public bool $isPublic = false;

    public ?int $selectedTagId = null;

    /** @var string[] */
    public array $selectedLocations = [];

    /** @var array<int, mixed> */
    public array $attachments = [];

    /** @var array<int, array{id: int, filename: string, is_public: bool, downloads_count: int}> */
    public array $storedDocuments = [];

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
            'selectedTagId' => ['nullable', 'integer', Rule::exists('notice_tags', 'id')],
            'isPublic' => 'boolean',
            'selectedLocations' => $selectedLocationsRule,
            'selectedLocations.*' => $selectedLocationRules,
            'attachments' => ['array'],
            'attachments.*' => ['file', 'mimes:pdf,docx,xlsx,jpg,jpeg,png', 'max:20480'],
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

        $notice = Notice::with(['locations.location', 'documents' => fn ($query) => $query->withCount('downloads')])->findOrFail($id);

        $this->editingId = $notice->id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedTagId = $notice->notice_tag_id;
        $this->selectedLocations = $notice->locations
            ->map(fn (NoticeLocation $location): ?string => $location->location_code)
            ->filter()
            ->values()
            ->all();
        $this->attachments = [];
        $this->setStoredDocumentsFromNotice($notice);

        $user = $this->currentUser();

        if ($user->hasRole(Role::GENERAL_ADMIN)) {
            $this->selectedLocations = [];
        }

        if ($user->hasRole(Role::COMMUNITY_ADMIN)) {
            $allowedLocationCodes = $this->allowedLocationCodes();

            $this->selectedLocations = collect($this->selectedLocations)
                ->filter(static fn (string $code): bool => in_array($code, $allowedLocationCodes, true))
                ->values()
                ->all();
        }

        $this->normalizeSelectedTagForCurrentUser();

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
                ->filter(static fn (string $code): bool => in_array($code, $allowedLocationCodes, true))
                ->values()
                ->all();
        }

        $this->validate();
        $this->authorizeSelectedTag();

        $notice = $this->upsertNotice();

        $this->syncLocations($notice);
        $this->storeAttachments($notice);
        $this->setStoredDocumentsFromNotice($notice->load(['documents' => fn ($query) => $query->withCount('downloads')]));

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
            'notice_tag_id' => $this->selectedTagId,
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
            $notice = Notice::query()->findOrFail($this->confirmingDeleteId);
            $notice->documents()->delete();
            $notice->delete();
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
        $this->selectedTagId = null;
        $this->selectedLocations = [];
        $this->attachments = [];
        $this->storedDocuments = [];
        $this->resetValidation();
    }

    private function authorizeSelectedTag(): void
    {
        $user = $this->currentUser();

        if ($user === null || ! $user->hasRole(Role::CONSTRUCTION_MANAGER) || $this->selectedTagId === null) {
            return;
        }

        $allowedTagIds = $this->availableNoticeTags()->pluck('id')->all();

        abort_unless(in_array($this->selectedTagId, $allowedTagIds, true), 403);
    }

    private function normalizeSelectedTagForCurrentUser(): void
    {
        $user = $this->currentUser();

        if ($user === null || ! $user->hasRole(Role::CONSTRUCTION_MANAGER) || $this->selectedTagId === null) {
            return;
        }

        $allowedTagIds = $this->availableNoticeTags()->pluck('id')->all();

        if (! in_array($this->selectedTagId, $allowedTagIds, true)) {
            $this->selectedTagId = null;
        }
    }

    /**
     * @return Collection<int, NoticeTag>
     */
    private function availableNoticeTags()
    {
        $user = $this->currentUser();

        if ($user?->hasRole(Role::CONSTRUCTION_MANAGER)) {
            $slugs = $user->constructions()
                ->pluck('constructions.slug')
                ->map(static fn (string $slug): string => 'obra-' . $slug)
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

    public function render(): View
    {
        $user = $this->currentUser();

        abort_unless($user?->canManageNotices(), 403);

        $allowedSortColumns = ['created_at', 'is_public', 'published_at'];
        $sortColumn = in_array($this->sortColumn, $allowedSortColumns, true) ? $this->sortColumn : 'published_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc'], true) ? $this->sortDir : 'desc';

        $notices = Notice::with([
            'locations.location',
            'tag',
            'documents' => fn ($query) => $query->withCount('downloads'),
        ])
            ->when($user->hasRole(Role::GENERAL_ADMIN), static function ($query): void {
                $query->whereDoesntHave('locations');
            })
            ->when($user->hasRole(Role::COMMUNITY_ADMIN), function ($query) use ($user): void {
                $managedLocationIds = $user->managedLocations()->pluck('locations.id')->all();

                if ($managedLocationIds === []) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query->whereHas('locations', function ($locationsQuery) use ($managedLocationIds): void {
                    $locationsQuery->whereIn('location_id', $managedLocationIds);
                });
            })
            ->orderBy($sortColumn, $sortDir)
            ->paginate(12);

        return view('livewire.admin.notice-manager', [
            'notices' => $notices,
            'allLocations' => $this->allLocationOptions(),
            'noticeTags' => $this->availableNoticeTags(),
        ]);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
