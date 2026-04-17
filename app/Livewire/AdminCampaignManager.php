<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Campaign;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\CampaignDocument;
use App\Models\CampaignLocation;
use App\Models\CampaignTemplate;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use App\Support\CampaignAdminOptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Services\Messaging\RecipientResolver;
use App\Livewire\Concerns\HandlesCampaignManagerActions;
use App\Livewire\Concerns\HandlesCampaignManagerPayload;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class AdminCampaignManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use HandlesCampaignManagerActions;
    use HandlesCampaignManagerPayload;
    use WithFileUploads;
    use WithPagination;

    public ?int $editingId = null;

    public string $subjectEu = '';

    public string $subjectEs = '';

    public string $bodyEu = '';

    public string $bodyEs = '';

    public string $channel = 'email';

    /** @var array<int, string> */
    public array $recipientFilters = [];

    public string $selectedTemplateId = '';

    public ?string $scheduledAt = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    /** @var array<int, array{id: int, filename: string}> */
    public array $storedAttachments = [];

    public bool $showForm = false;

    public string $sortColumn = 'created_at';

    public string $sortDir = 'desc';

    public int $recipientCountTotal = 0;

    /** @var array{coprop1: int, coprop2: int} */
    public array $recipientCountBySlot = [
        'coprop1' => 0,
        'coprop2' => 0,
    ];

    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    public ?int $confirmingActionId = null;

    public string $confirmingAction = '';

    public bool $showActionModal = false;

    private RecipientResolver $recipientResolver;

    public function boot(RecipientResolver $recipientResolver): void
    {
        $this->recipientResolver = $recipientResolver;
    }

    public function mount(): void
    {
        $this->authorizeViewAny();

        $this->recipientFilters = $this->options()->defaultRecipientFilters();

        $editCampaignId = (int) request()->integer('editCampaign');

        if ($editCampaignId > 0) {
            $this->editCampaign($editCampaignId);

            return;
        }

        $this->recalculateRecipients();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $this->authorizeCreate();

        return [
            ...$this->contentRules(),
            'recipientFilters' => ['required', 'array', 'min:1'],
            'recipientFilters.*' => ['required', 'string', Rule::in($this->options()->allowedRecipientFilters())],
            'selectedTemplateId' => ['nullable', 'string', Rule::exists('campaign_templates', 'id')],
            'scheduledAt' => ['nullable', 'date'],
            'attachments' => ['array'],
            'attachments.*' => ['file', 'mimes:pdf,docx,xlsx,jpg,jpeg,png', 'max:20480'],
        ];
    }

    public function createCampaign(): void
    {
        $this->authorizeCreate();

        $this->resetForm();
        $this->showForm = true;
    }

    public function editCampaign(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->with(['documents', 'locations'])->findOrFail($id);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $this->editingId = $campaign->id;
        $this->subjectEu = (string) ($campaign->subject_eu ?? '');
        $this->subjectEs = (string) ($campaign->subject_es ?? '');
        $this->bodyEu = (string) ($campaign->body_eu ?? '');
        $this->bodyEs = (string) ($campaign->body_es ?? '');
        $this->channel = (string) $campaign->channel;
        $this->recipientFilters = $campaign->locations
            ->pluck('location_id')
            ->map(static fn (int $locationId): string => (string) $locationId)
            ->values()
            ->all();

        if ($this->recipientFilters === []) {
            $this->recipientFilters = ['all'];
        }

        $scheduledAt = (string) ($campaign->scheduled_at ?? '');
        $this->scheduledAt = $scheduledAt !== '' && strtotime($scheduledAt) !== false
            ? date('Y-m-d\TH:i', strtotime($scheduledAt))
            : null;

        $this->attachments = [];
        $this->storedAttachments = $campaign->documents
            ->map(fn (CampaignDocument $document): array => [
                'id' => $document->id,
                'filename' => $document->filename,
            ])
            ->values()
            ->all();
        $this->showForm = true;

        $this->recalculateRecipients();
    }

    public function saveCampaign(): void
    {
        $this->validate();

        $campaign = $this->upsertCampaign();

        $this->syncCampaignLocations($campaign);

        $this->storeAttachments($campaign);

        $this->resetForm();
        $this->showForm = false;

        session()->flash('message', __('general.messages.saved'));
    }

    public function saveAsTemplate(): void
    {
        $this->authorizeCreate();
        $this->validate($this->contentRules());

        $template = CampaignTemplate::query()->create([
            ...$this->templatePayload(),
            'created_by_user_id' => $this->currentUser()?->id,
        ]);

        $this->selectedTemplateId = (string) $template->id;

        session()->flash('message', __('general.messages.saved'));
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

    public function updatedChannel(): void
    {
        $this->recalculateRecipients();
    }

    public function updatedRecipientFilters(): void
    {
        $this->recalculateRecipients();
    }

    public function removePendingAttachment(int $index): void
    {
        $this->authorizeViewAny();

        if (! array_key_exists($index, $this->attachments)) {
            return;
        }

        unset($this->attachments[$index]);

        $this->attachments = array_values($this->attachments);
    }

    public function removeStoredAttachment(int $documentId): void
    {
        $this->authorizeViewAny();

        if ($this->editingId === null) {
            return;
        }

        $campaign = Campaign::query()->with('documents')->findOrFail($this->editingId);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $document = $campaign->documents->firstWhere('id', $documentId);

        if (! $document instanceof CampaignDocument) {
            abort(404);
        }

        Storage::disk('public')->delete($document->path);
        $document->delete();

        $this->storedAttachments = array_values(array_filter(
            $this->storedAttachments,
            static fn (array $attachment): bool => (int) $attachment['id'] !== $documentId,
        ));

        session()->flash('message', __('general.messages.deleted'));
    }

    public function updatedSelectedTemplateId(string $value): void
    {
        if ($value === '') {
            return;
        }

        $template = CampaignTemplate::query()->find($value);

        if ($template === null) {
            return;
        }

        $this->subjectEu = (string) ($template->subject_eu ?? '');
        $this->subjectEs = (string) ($template->subject_es ?? '');
        $this->bodyEu = (string) ($template->body_eu ?? '');
        $this->bodyEs = (string) ($template->body_es ?? '');
        $this->channel = (string) $template->channel;

        $this->recalculateRecipients();
    }

    public function render(): View
    {
        $this->authorizeViewAny();

        $allowedSortColumns = ['created_at', 'status', 'scheduled_at', 'sent_at'];
        $sortColumn = in_array($this->sortColumn, $allowedSortColumns, true) ? $this->sortColumn : 'created_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc'], true) ? $this->sortDir : 'desc';

        $campaignsQuery = Campaign::query()->withCount('recipients');

        $campaignsQuery->with(['locations.location']);

        $this->applyCampaignVisibilityScope($campaignsQuery);

        $campaigns = $campaignsQuery
            ->orderBy($sortColumn, $sortDir)
            ->paginate(12);

        $options = $this->options();

        return view('livewire.admin.campaign-manager', [
            'campaigns' => $campaigns,
            'channelOptions' => $options->channelOptions(),
            'templateOptions' => $options->templateOptions(),
            'recipientFilterOptions' => $options->recipientFilterOptions(),
            'options' => $options,
            'previewSubject' => $options->previewText($this->subjectEu, $this->subjectEs),
            'previewBody' => $options->previewText($this->bodyEu, $this->bodyEs),
        ]);
    }

    private function recalculateRecipients(): void
    {
        $this->recipientCountTotal = 0;
        $this->recipientCountBySlot = ['coprop1' => 0, 'coprop2' => 0];

        if ($this->channel === '' || $this->recipientFilters === []) {
            return;
        }

        $campaign = new Campaign([
            'channel' => $this->channel,
        ]);

        $campaign->setRelation('locations', $this->campaignLocationRelationRows());

        $rows = $this->recipientResolver->resolve($campaign);

        $this->recipientCountTotal = $rows->count();
        $this->recipientCountBySlot['coprop1'] = $rows->where('slot', 'coprop1')->count();
        $this->recipientCountBySlot['coprop2'] = $rows->where('slot', 'coprop2')->count();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->subjectEu = '';
        $this->subjectEs = '';
        $this->bodyEu = '';
        $this->bodyEs = '';
        $this->channel = 'email';
        $this->recipientFilters = $this->options()->defaultRecipientFilters();
        $this->selectedTemplateId = '';
        $this->scheduledAt = null;
        $this->attachments = [];
        $this->storedAttachments = [];
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;

        $this->resetValidation();
        $this->recalculateRecipients();
    }

    /**
     * @param  Builder<Campaign>  $query
     */
    private function applyCampaignVisibilityScope(Builder $query): void
    {
        $accessScope = $this->currentUser()?->campaignAccessScope() ?? 'none';

        if ($accessScope === 'all-only') {
            $query->whereDoesntHave('locations');

            return;
        }

        if ($accessScope !== 'managed-locations') {
            return;
        }

        $allowedLocationIds = $this->options()->allowedManagedLocationIds();

        if ($allowedLocationIds === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query
            ->whereHas('locations', function (Builder $locationsQuery): void {
                $locationsQuery->whereNull('campaign_locations.deleted_at');
            })
            ->whereDoesntHave('locations', function (Builder $locationsQuery) use ($allowedLocationIds): void {
                $locationsQuery
                    ->whereNull('campaign_locations.deleted_at')
                    ->whereNotIn('location_id', $allowedLocationIds);
            });
    }

    /**
     * @return array<int, int>
     */
    private function selectedLocationIds(): array
    {
        $allowedFilters = collect($this->options()->allowedRecipientFilters())
            ->map(static fn (string $filter): int => (int) $filter)
            ->filter(static fn (int $filter): bool => $filter > 0)
            ->values()
            ->all();

        return collect($this->recipientFilters)
            ->map(static fn (string $value): int => (int) $value)
            ->filter(static fn (int $locationId): bool => in_array($locationId, $allowedFilters, true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, CampaignLocation>
     */
    private function campaignLocationRelationRows()
    {
        return collect($this->selectedLocationIds())
            ->map(static function (int $locationId): CampaignLocation {
                return new CampaignLocation([
                    'location_id' => $locationId,
                    'deleted_at' => null,
                ]);
            })
            ->values();
    }

    private function softDeleteAllCampaignLocations(Campaign $campaign): void
    {
        CampaignLocation::query()
            ->where('campaign_id', $campaign->id)
            ->whereNull('deleted_at')
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private function syncCampaignLocations(Campaign $campaign): void
    {
        if (in_array('all', $this->recipientFilters, true)) {
            $this->softDeleteAllCampaignLocations($campaign);

            return;
        }

        $locationIds = $this->selectedLocationIds();

        if ($locationIds === []) {
            $this->softDeleteAllCampaignLocations($campaign);

            return;
        }

        CampaignLocation::query()
            ->where('campaign_id', $campaign->id)
            ->whereNull('deleted_at')
            ->whereNotIn('location_id', $locationIds)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        $upsertRows = collect($locationIds)
            ->map(static function (int $locationId) use ($campaign): array {
                return [
                    'campaign_id' => $campaign->id,
                    'location_id' => $locationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'deleted_at' => null,
                ];
            })
            ->all();

        CampaignLocation::upsert(
            $upsertRows,
            ['campaign_id', 'location_id'],
            ['updated_at', 'deleted_at'],
        );
    }

    private function options(): CampaignAdminOptions
    {
        return CampaignAdminOptions::forUser($this->currentUser());
    }
}
