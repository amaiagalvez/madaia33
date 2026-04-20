<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Owner;
use App\Models\Notice;
use Livewire\Component;
use App\Models\NoticeRead;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\NoticeLocation;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\LocalizedDateFormatter;
use App\Concerns\HandlesNoticeDocuments;
use App\Concerns\ManagesNoticeLocations;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Livewire\Concerns\ManagesAdminNoticeFilters;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class AdminNoticeManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use HandlesNoticeDocuments;
    use ManagesAdminNoticeFilters;
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

    public string $publishedAt = '';

    public string $originalPublishedAt = '';

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

    public bool $showReadersModal = false;

    public ?int $readersNoticeId = null;

    /** @var array<int, array{owner_name: string, opened_at: string, has_opened: bool}> */
    public array $noticeReaders = [];

    public string $sortColumn = 'published_at';

    public string $sortDir = 'desc';

    public string $search = '';

    public string $tagFilter = 'all';

    // UI state
    public bool $showForm = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $user = $this->currentUser();

        abort_unless($user?->canManageNotices(), 403);

        $selectedLocationRules = ['string', 'exists:locations,id'];

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
            'publishedAt' => 'nullable|date',
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

        $notice = Notice::with(['locations.location', 'documents' => fn($query) => $query->withCount('downloads')])->findOrFail($id);

        $this->editingId = $notice->id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedTagId = $notice->notice_tag_id;
        $this->publishedAt = $notice->published_at?->format('Y-m-d') ?? '';
        $this->originalPublishedAt = $notice->published_at?->toDateTimeString() ?? '';
        $this->selectedLocations = $notice->locations
            ->map(fn(NoticeLocation $location): string => (string) $location->location_id)
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
                ->filter(static fn(string $code): bool => in_array($code, $allowedLocationCodes, true))
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
                ->filter(static fn(string $code): bool => in_array($code, $allowedLocationCodes, true))
                ->values()
                ->all();
        }

        $this->validate();
        $this->authorizeSelectedTag();

        $notice = $this->upsertNotice();

        $this->syncLocations($notice);
        $this->storeAttachments($notice);
        $this->setStoredDocumentsFromNotice($notice->load(['documents' => fn($query) => $query->withCount('downloads')]));

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
        $selectedPublishedAt = $this->selectedPublishedAt();

        if ($this->shouldKeepOriginalPublishedAt($existingNotice, $selectedPublishedAt)) {
            return $existingNotice?->published_at;
        }

        if ($selectedPublishedAt !== null) {
            return $selectedPublishedAt;
        }

        return $this->fallbackPublishedAt($existingNotice);
    }

    private function selectedPublishedAt(): ?Carbon
    {
        if ($this->publishedAt === '') {
            return null;
        }

        return Carbon::parse($this->publishedAt)->startOfDay();
    }

    private function shouldKeepOriginalPublishedAt(?Notice $existingNotice, ?Carbon $selectedPublishedAt): bool
    {
        if ($existingNotice === null || $selectedPublishedAt === null || $this->originalPublishedAt === '') {
            return false;
        }

        return Carbon::parse($this->originalPublishedAt)->toDateString() === $selectedPublishedAt->toDateString();
    }

    private function fallbackPublishedAt(?Notice $existingNotice): mixed
    {
        if ($existingNotice === null) {
            return $this->isPublic ? now() : null;
        }

        if (! $this->isPublic) {
            return $existingNotice->published_at;
        }

        return $existingNotice->published_at ?: now();
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

    public function showReaders(int $id): void
    {
        abort_unless($this->currentUser()?->canManageNotices(), 403);

        $notice = Notice::query()
            ->with('tag.construction')
            ->findOrFail($id);

        abort_unless($notice->tag?->construction !== null, 404);

        $this->readersNoticeId = $id;

        $readsByOwnerId = NoticeRead::query()
            ->where('notice_id', $notice->id)
            ->get(['owner_id', 'opened_at'])
            ->keyBy('owner_id');

        $this->noticeReaders = Owner::query()
            ->whereHas('activeAssignments')
            ->orderBy('coprop1_name')
            ->orderBy('coprop1_surname')
            ->get(['id', 'coprop1_name', 'coprop1_surname'])
            ->map(static function (Owner $owner) use ($readsByOwnerId): array {
                /** @var NoticeRead|null $read */
                $read = $readsByOwnerId->get($owner->id);

                return [
                    'owner_name' => $owner->fullName1 !== '' ? $owner->fullName1 : '—',
                    'opened_at' => LocalizedDateFormatter::shortDateTime($read?->opened_at),
                    'has_opened' => $read !== null,
                ];
            })
            ->values()
            ->all();

        $this->showReadersModal = true;
    }

    public function closeReadersModal(): void
    {
        $this->showReadersModal = false;
        $this->readersNoticeId = null;
        $this->noticeReaders = [];
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
        $this->publishedAt = '';
        $this->originalPublishedAt = '';
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

    public function render(): View
    {
        $user = $this->currentUser();

        abort_unless($user?->canManageNotices(), 403);

        return view('livewire.admin.notice-manager', [
            'notices' => $this->noticesQuery($user)->paginate(12),
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
