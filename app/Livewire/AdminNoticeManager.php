<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Notice;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use App\Models\NoticeLocation;
use Illuminate\Contracts\View\View;
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
        $notice = Notice::with(['locations.location', 'locations.property.location'])->findOrFail($id);

        $this->editingId = $notice->id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedLocations = $notice->locations
            ->map(function (NoticeLocation $location): ?string {
                if ($location->property_id !== null) {
                    return $location->location_code;
                }

                return $location->location_code;
            })
            ->filter()
            ->values()
            ->all();
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

        foreach (array_values(array_unique($this->selectedLocations)) as $selected) {
            [$locationId, $propertyId] = $this->resolveSelectionToForeignKeys((string) $selected);

            if ($locationId === null && $propertyId === null) {
                continue;
            }

            $rows[] = [
                'notice_id' => $notice->id,
                'location_id' => $locationId,
                'property_id' => $propertyId,
            ];
        }

        if ($rows !== []) {
            NoticeLocation::insert($rows);
        }
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    private function resolveSelectionToForeignKeys(string $selected): array
    {
        $locationId = Location::query()
            ->where('code', $selected)
            ->value('id');

        return [$locationId, null];
    }

    /**
     * @return array<int, array{code: string, type: string, label: string}>
     */
    private function allLocationOptions(): array
    {
        $locations = Location::query()
            ->whereIn('type', ['portal', 'garage'])
            ->orderByRaw("CASE WHEN type = 'portal' THEN 1 WHEN type = 'garage' THEN 2 ELSE 3 END")
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
            'garage' => __('admin.locations.types.garage') . ' ',
            'storage' => __('admin.locations.types.storage') . ' ',
            default => '',
        };
    }

    public function render(): View
    {
        $notices = Notice::with(['locations.location', 'locations.property.location'])->latest()->paginate(12);

        return view('livewire.admin.notice-manager', [
            'notices' => $notices,
            'allLocations' => $this->allLocationOptions(),
        ]);
    }
}
