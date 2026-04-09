<?php

namespace App\Livewire;

use App\Models\Notice;
use Livewire\Component;
use App\CommunityLocations;
use Illuminate\Support\Str;
use App\Models\NoticeLocation;
use Illuminate\Contracts\View\View;
use App\Concerns\BuildsLocaleFieldConfigs;

class AdminNoticeManager extends Component
{
    use BuildsLocaleFieldConfigs;

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

    // UI state
    public bool $showForm = false;

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'titleEu' => 'required|string|max:255',
            'titleEs' => 'nullable|string|max:255',
            'contentEu' => 'required|string',
            'contentEs' => 'nullable|string',
            'isPublic' => 'boolean',
            'selectedLocations' => 'array',
            'selectedLocations.*' => 'string',
        ];
    }

    public function createNotice(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function editNotice(int $id): void
    {
        $notice = Notice::with('locations')->findOrFail($id);

        $this->editingId = $notice->id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedLocations = $notice->locations->pluck('location_code')->toArray();
        $this->showForm = true;
        $this->dispatch('admin-notice-form-focus');
    }

    public function saveNotice(): void
    {
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
        $notice = Notice::findOrFail($id);
        $notice->update([
            'is_public' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublishNotice(int $id): void
    {
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

        foreach (array_values(array_unique($this->selectedLocations)) as $code) {
            $rows[] = [
                'notice_id' => $notice->id,
                'location_type' => CommunityLocations::typeForCode($code),
                'location_code' => $code,
            ];
        }

        if ($rows !== []) {
            NoticeLocation::insert($rows);
        }
    }

    public function render(): View
    {
        $notices = Notice::with('locations')->latest()->get();

        return view('livewire.admin.notice-manager', [
            'notices' => $notices,
            'allLocations' => CommunityLocations::options(__('notices.portal'), __('notices.garage')),
        ]);
    }
}
