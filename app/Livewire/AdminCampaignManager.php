<?php

namespace App\Livewire;

use App\Models\Owner;
use App\Models\Role;
use App\Models\Setting;
use Livewire\Component;
use App\Models\Campaign;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\CampaignDocument;
use App\Models\CampaignLocation;
use App\Models\CampaignTemplate;
use App\Models\CampaignRecipient;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use App\Support\CampaignAdminOptions;
use App\Support\ConfiguredMailSettings;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Contracts\Messaging\EmailProvider;
use App\Services\Messaging\RecipientResolver;
use App\Services\Messaging\MessageVariableResolver;
use App\Livewire\Concerns\HandlesCampaignManagerActions;
use App\Livewire\Concerns\HandlesCampaignManagerPayload;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * @SuppressWarnings("PHPMD.ExcessiveClassLength")
 */
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

    public ?int $schedulingCampaignId = null;

    public string $scheduleAtInput = '';

    public bool $showScheduleModal = false;

    public bool $showTestEmailModal = false;

    public string $testEmailAddress = '';

    public bool $hasUnsavedChanges = false;

    /** @var array<string, mixed> */
    public array $savedFormSnapshot = [];

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
        $this->syncSavedFormSnapshot();
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'editingId',
            'showForm',
            'showDeleteModal',
            'confirmingDeleteId',
            'confirmingActionId',
            'confirmingAction',
            'showActionModal',
            'schedulingCampaignId',
            'scheduleAtInput',
            'showScheduleModal',
            'showTestEmailModal',
            'testEmailAddress',
            'sortColumn',
            'sortDir',
            'recipientCountTotal',
        ], true) || str_starts_with($property, 'recipientCountBySlot.')) {
            return;
        }

        $this->refreshUnsavedChangesState();
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

        // Bloquear acceso a campaign id=1 para no SUPER_ADMIN
        if ($id === 1 && ! $this->currentUser()?->hasRole(Role::SUPER_ADMIN)) {
            abort(403);
        }

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
            ->map(static fn(int $locationId): string => (string) $locationId)
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
            ->map(fn(CampaignDocument $document): array => [
                'id' => $document->id,
                'filename' => $document->filename,
            ])
            ->values()
            ->all();
        $this->showForm = true;

        $this->recalculateRecipients();
        $this->syncSavedFormSnapshot();
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

    public function openTestEmailModal(): void
    {
        $this->authorizeTestEmailAction();

        if (! $this->ensureNoUnsavedChangesBeforeTestEmail()) {
            return;
        }

        $this->testEmailAddress = '';
        $this->showTestEmailModal = true;
    }

    public function closeTestEmailModal(): void
    {
        $this->showTestEmailModal = false;
        $this->testEmailAddress = '';
    }

    /**
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     */
    public function sendTestEmail(): void
    {
        $this->authorizeTestEmailAction();

        if (! $this->ensureNoUnsavedChangesBeforeTestEmail()) {
            return;
        }

        $this->validate([
            ...$this->contentRules(),
            'testEmailAddress' => ['required', 'email'],
        ]);

        if ($this->channel !== 'email') {
            $this->addError('channel', __('campaigns.admin.test_email.only_email_channel'));

            return;
        }

        $mailSettings = Setting::stringValues([
            'from_address',
            'from_name',
            'smtp_host',
            'smtp_port',
            'smtp_username',
            'smtp_password',
            'smtp_encryption',
        ]);

        if (trim($mailSettings['smtp_host'] ?? '') === '') {
            $this->addError('testEmailAddress', __('campaigns.admin.test_email.smtp_not_configured'));

            return;
        }

        $fromAddress = trim((string) ($mailSettings['from_address'] ?? config('mail.from.address', '')));
        $fromName = trim((string) ($mailSettings['from_name'] ?? config('mail.from.name', '')));

        if ($fromAddress === '') {
            $this->addError('testEmailAddress', __('campaigns.admin.test_email.from_not_configured'));

            return;
        }

        app(ConfiguredMailSettings::class)->apply($mailSettings);

        $campaignForPreview = $this->campaignForPreview();
        $previewRecipientData = $this->previewRecipientData($campaignForPreview);
        $resolver = app(MessageVariableResolver::class);
        $emailProvider = app(EmailProvider::class);

        $initialLocale = app()->getLocale();

        try {
            foreach (['eu', 'es'] as $locale) {
                app()->setLocale($locale);

                [$subjectText, $htmlBody] = $this->localizedTestEmailContent($locale);

                if ($previewRecipientData !== null) {
                    $subjectText = $resolver->resolve(
                        $subjectText,
                        $previewRecipientData['owner'],
                        $previewRecipientData['slot'],
                    );

                    $htmlBody = $resolver->resolve(
                        $htmlBody,
                        $previewRecipientData['owner'],
                        $previewRecipientData['slot'],
                    );
                }

                $prefixedSubject = $this->prefixedTestSubject($subjectText, $locale);

                $previewRecipient = $this->buildPreviewRecipient(
                    $campaignForPreview,
                    $previewRecipientData['owner'] ?? null,
                    $previewRecipientData['slot'] ?? 'coprop1',
                    $this->testEmailAddress,
                );

                $emailProvider->send($previewRecipient, $prefixedSubject, $htmlBody);
            }

            session()->flash('message', __('campaigns.admin.test_email.sent'));
            $this->closeTestEmailModal();
        } catch (\Throwable $exception) {
            $this->addError('testEmailAddress', __('campaigns.admin.test_email.failed', ['error' => $exception->getMessage()]));
        } finally {
            app()->setLocale($initialLocale);
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
        $this->refreshUnsavedChangesState();
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
            static fn(array $attachment): bool => (int) $attachment['id'] !== $documentId,
        ));

        $this->syncSavedFormSnapshot();

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
        $this->refreshUnsavedChangesState();
    }

    public function render(): View
    {
        $this->authorizeViewAny();

        $allowedSortColumns = ['created_at', 'status', 'scheduled_at', 'sent_at'];
        $sortColumn = in_array($this->sortColumn, $allowedSortColumns, true) ? $this->sortColumn : 'created_at';
        $sortDir = in_array($this->sortDir, ['asc', 'desc'], true) ? $this->sortDir : 'desc';

        $campaignsQuery = Campaign::query()->withCount([
            'recipients',
            'recipients as opened_recipients_count' => fn(Builder $query): Builder => $query->whereHas(
                'trackingEvents',
                fn(Builder $events): Builder => $events->where('event_type', 'open'),
            ),
        ]);

        $campaignsQuery->with(['locations.location']);

        $this->applyCampaignVisibilityScope($campaignsQuery);

        // Filtrar campaign id=1 solo para SUPER_ADMIN
        if (! $this->currentUser()?->hasRole(Role::SUPER_ADMIN)) {
            $campaignsQuery->where('id', '!=', 1);
        }

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
        $this->showTestEmailModal = false;
        $this->testEmailAddress = '';
        $this->hasUnsavedChanges = false;
        $this->savedFormSnapshot = [];

        $this->resetValidation();
        $this->recalculateRecipients();
        $this->syncSavedFormSnapshot();
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function localizedTestEmailContent(string $locale): array
    {
        $subject = $locale === 'eu'
            ? $this->fallbackLocalizedValue($this->subjectEu, $this->subjectEs)
            : $this->fallbackLocalizedValue($this->subjectEs, $this->subjectEu);

        $body = $locale === 'eu'
            ? $this->fallbackLocalizedValue($this->bodyEu, $this->bodyEs)
            : $this->fallbackLocalizedValue($this->bodyEs, $this->bodyEu);

        return [$subject, $body];
    }

    private function fallbackLocalizedValue(?string $primary, ?string $fallback): string
    {
        return (string) ($this->normalizeNullableValue((string) ($primary ?? ''))
            ?? $this->normalizeNullableValue((string) ($fallback ?? ''))
            ?? '');
    }

    private function prefixedTestSubject(string $subject, string $locale): string
    {
        $prefix = $locale === 'eu' ? '[FROGA]' : '[PRUEBA]';
        $trimmedSubject = trim($subject);

        if ($trimmedSubject === '') {
            return $prefix;
        }

        return $prefix . ' ' . $trimmedSubject;
    }

    private function campaignForPreview(): Campaign
    {
        $campaign = $this->editingId !== null
            ? Campaign::query()->with(['documents', 'locations'])->findOrFail($this->editingId)
            : new Campaign;

        $campaign->forceFill($this->contentPayload());

        $campaign->setRelation('locations', $this->campaignLocationRelationRows());

        if (! $campaign->relationLoaded('documents')) {
            $campaign->setRelation('documents', collect());
        }

        return $campaign;
    }

    /**
     * @return array{owner: Owner, slot: string}|null
     */
    private function previewRecipientData(Campaign $campaign): ?array
    {
        $recipientRow = $this->recipientResolver->resolve($campaign)->first();

        if (! is_array($recipientRow)) {
            return null;
        }

        $ownerId = (int) $recipientRow['owner_id'];
        $slot = (string) $recipientRow['slot'];

        if ($ownerId <= 0 || ! in_array($slot, ['coprop1', 'coprop2'], true)) {
            return null;
        }

        $owner = Owner::query()->find($ownerId);

        if (! $owner instanceof Owner) {
            return null;
        }

        return [
            'owner' => $owner,
            'slot' => $slot,
        ];
    }

    private function buildPreviewRecipient(Campaign $campaign, ?Owner $owner, string $slot, string $contact): CampaignRecipient
    {
        $recipient = new CampaignRecipient([
            'campaign_id' => $campaign->id,
            'owner_id' => $owner?->id,
            'slot' => $slot,
            'contact' => $contact,
            'tracking_token' => bin2hex(random_bytes(32)),
            'status' => 'pending',
            'error_message' => null,
        ]);

        $recipient->setRelation('campaign', $campaign);

        if ($owner instanceof Owner) {
            $recipient->setRelation('owner', $owner);
        }

        return $recipient;
    }

    private function authorizeTestEmailAction(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        if ($this->editingId !== null) {
            $campaign = Campaign::query()->findOrFail($this->editingId);

            $this->authorize('update', $campaign);

            return;
        }

        $this->authorize('create', Campaign::class);
    }

    private function ensureNoUnsavedChangesBeforeTestEmail(): bool
    {
        if (! $this->hasUnsavedChanges) {
            return true;
        }

        $this->addError('sendTestEmail', __('admin.settings_form.save_before_test_email'));

        return false;
    }

    private function refreshUnsavedChangesState(): void
    {
        $this->hasUnsavedChanges = $this->currentFormSnapshot() !== $this->savedFormSnapshot;

        if (! $this->hasUnsavedChanges) {
            $this->resetErrorBag('sendTestEmail');
        }
    }

    private function syncSavedFormSnapshot(): void
    {
        $this->savedFormSnapshot = $this->currentFormSnapshot();
        $this->hasUnsavedChanges = false;
        $this->resetErrorBag('sendTestEmail');
    }

    /**
     * @return array<string, mixed>
     */
    private function currentFormSnapshot(): array
    {
        return [
            'campaign' => $this->campaignPayload(),
            'recipient_filters' => $this->normalizedRecipientFilters(),
            'stored_attachment_ids' => collect($this->storedAttachments)
                ->pluck('id')
                ->map(static fn(mixed $id): int => (int) $id)
                ->sort()
                ->values()
                ->all(),
            'pending_attachment_names' => collect($this->attachments)
                ->map(static fn($attachment): string => $attachment->getClientOriginalName())
                ->sort()
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function normalizedRecipientFilters(): array
    {
        if (in_array('all', $this->recipientFilters, true)) {
            return ['all'];
        }

        return collect($this->selectedLocationIds())
            ->map(static fn(int $locationId): string => (string) $locationId)
            ->sort()
            ->values()
            ->all();
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
            ->map(static fn(string $filter): int => (int) $filter)
            ->filter(static fn(int $filter): bool => $filter > 0)
            ->values()
            ->all();

        return collect($this->recipientFilters)
            ->map(static fn(string $value): int => (int) $value)
            ->filter(static fn(int $locationId): bool => in_array($locationId, $allowedFilters, true))
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
