<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Campaign;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\CampaignDocument;
use App\Models\CampaignTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Support\CampaignAdminOptions;
use Illuminate\Support\Facades\Storage;
use App\Concerns\BuildsLocaleFieldConfigs;
use App\Jobs\Messaging\DispatchCampaignJob;
use App\Services\Messaging\RecipientResolver;
use App\Actions\Campaigns\DuplicateCampaignAction;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class AdminCampaignManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithFileUploads;
    use WithPagination;

    public ?int $editingId = null;

    public string $subjectEu = '';

    public string $subjectEs = '';

    public string $bodyEu = '';

    public string $bodyEs = '';

    public string $channel = 'email';

    public string $recipientFilter = 'all';

    public string $selectedTemplateId = '';

    public ?string $scheduledAt = null;

    /** @var array<int, UploadedFile> */
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

    public function mount(): void
    {
        $this->authorizeViewAny();

        if ($this->currentUser()?->hasRole(Role::COMMUNITY_ADMIN)) {
            $this->recipientFilter = $this->options()->defaultRecipientFilter();
        }

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
            'recipientFilter' => ['required', 'string', Rule::in($this->options()->allowedRecipientFilters())],
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

        $campaign = Campaign::query()->with('documents')->findOrFail($id);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $this->editingId = $campaign->id;
        $this->subjectEu = (string) ($campaign->subject_eu ?? '');
        $this->subjectEs = (string) ($campaign->subject_es ?? '');
        $this->bodyEu = (string) ($campaign->body_eu ?? '');
        $this->bodyEs = (string) ($campaign->body_es ?? '');
        $this->channel = (string) $campaign->channel;
        $this->recipientFilter = (string) $campaign->recipient_filter;

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
    }

    public function saveCampaign(): void
    {
        $this->validate();

        $campaign = $this->upsertCampaign();

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

    public function duplicateCampaign(int $id): void
    {
        $this->authorizeViewAny();

        $sourceCampaign = Campaign::query()->with('documents')->findOrFail($id);

        $this->authorize('duplicate', $sourceCampaign);

        $user = $this->currentUser();

        abort_if($user === null, 403);

        $newCampaign = app(DuplicateCampaignAction::class)->execute($sourceCampaign, $user);

        session()->flash('message', __('general.messages.saved'));

        $this->redirectRoute('admin.campaigns', ['editCampaign' => $newCampaign->id], navigate: true);
    }

    public function sendCampaign(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'draft', 403);

        dispatch(new DispatchCampaignJob($campaign->id));
    }

    public function scheduleCampaign(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'draft', 403);

        $when = now()->addMinutes(5);

        $campaign->update([
            'status' => 'scheduled',
            'scheduled_at' => $when,
        ]);
    }

    public function cancelSchedule(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        $this->authorize('send', $campaign);

        abort_unless($campaign->status === 'scheduled', 403);

        $campaign->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeViewAny();

        $campaign = Campaign::query()->findOrFail($id);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $this->confirmingDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function confirmAction(int $id, string $action): void
    {
        $this->authorizeViewAny();

        abort_unless(in_array($action, ['duplicate', 'send', 'schedule', 'cancel_schedule'], true), 404);

        $campaign = Campaign::query()->findOrFail($id);

        match ($action) {
            'duplicate' => $this->authorize('duplicate', $campaign),
            'send', 'schedule', 'cancel_schedule' => $this->authorize('send', $campaign),
        };

        if (in_array($action, ['send', 'schedule'], true)) {
            abort_unless($campaign->status === 'draft', 403);
        }

        if ($action === 'cancel_schedule') {
            abort_unless($campaign->status === 'scheduled', 403);
        }

        $this->confirmingActionId = $campaign->id;
        $this->confirmingAction = $action;
        $this->showActionModal = true;
    }

    public function cancelAction(): void
    {
        $this->confirmingActionId = null;
        $this->confirmingAction = '';
        $this->showActionModal = false;
    }

    public function doAction(): void
    {
        if ($this->confirmingActionId === null || $this->confirmingAction === '') {
            return;
        }

        $campaignId = $this->confirmingActionId;
        $action = $this->confirmingAction;

        $this->cancelAction();

        match ($action) {
            'duplicate' => $this->duplicateCampaign($campaignId),
            'send' => $this->sendCampaign($campaignId),
            'schedule' => $this->scheduleCampaign($campaignId),
            'cancel_schedule' => $this->cancelSchedule($campaignId),
            default => null,
        };
    }

    public function deleteCampaign(): void
    {
        $this->authorizeViewAny();

        if ($this->confirmingDeleteId === null) {
            return;
        }

        $campaign = Campaign::query()->findOrFail($this->confirmingDeleteId);

        abort_unless($this->canMutateCampaign($campaign), 403);

        $campaign->delete();

        $this->cancelDelete();

        session()->flash('message', __('general.messages.deleted'));
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

    public function updatedRecipientFilter(): void
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
            static fn(array $attachment): bool => (int) $attachment['id'] !== $documentId,
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

        $campaigns = Campaign::query()
            ->withCount('recipients')
            ->when($this->currentUser()?->hasRole(Role::COMMUNITY_ADMIN), function ($query): void {
                $allowedCodes = $this->options()->allowedManagedLocationCodes();

                $query->where('recipient_filter', '!=', 'all')
                    ->where(function ($filterQuery) use ($allowedCodes): void {
                        foreach ($allowedCodes as $locationCode) {
                            $filterQuery->orWhere('recipient_filter', 'portal:' . $locationCode)
                                ->orWhere('recipient_filter', 'garage:' . $locationCode);
                        }
                    });
            })
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

        if ($this->channel === '' || $this->recipientFilter === '') {
            return;
        }

        $filters = $this->options()->allowedRecipientFilters();

        if (! in_array($this->recipientFilter, $filters, true)) {
            return;
        }

        $campaign = new Campaign([
            'channel' => $this->channel,
            'recipient_filter' => $this->recipientFilter,
        ]);

        $rows = app(RecipientResolver::class)->resolve($campaign);

        $this->recipientCountTotal = $rows->count();
        $this->recipientCountBySlot['coprop1'] = $rows->where('slot', 'coprop1')->count();
        $this->recipientCountBySlot['coprop2'] = $rows->where('slot', 'coprop2')->count();
    }

    private function upsertCampaign(): Campaign
    {
        if ($this->editingId !== null) {
            $campaign = Campaign::query()->findOrFail($this->editingId);

            abort_unless($this->canMutateCampaign($campaign), 403);

            $campaign->update($this->campaignPayload());

            return $campaign;
        }

        return Campaign::query()->create([
            ...$this->campaignPayload(),
            'created_by_user_id' => $this->currentUser()?->id,
            'status' => 'draft',
            'sent_at' => null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignPayload(): array
    {
        return [
            ...$this->contentPayload(),
            'recipient_filter' => $this->recipientFilter,
            'scheduled_at' => $this->scheduledAt !== null && $this->scheduledAt !== '' ? $this->scheduledAt : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function templatePayload(): array
    {
        return [
            ...$this->contentPayload(),
            'name' => $this->templateName(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentPayload(): array
    {
        return [
            'subject_eu' => $this->normalizeNullableValue($this->subjectEu),
            'subject_es' => $this->normalizeNullableValue($this->subjectEs),
            'body_eu' => $this->normalizeNullableValue($this->bodyEu),
            'body_es' => $this->normalizeNullableValue($this->bodyEs),
            'channel' => $this->channel,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentRules(): array
    {
        return [
            'subjectEu' => ['nullable', 'string', 'max:255', 'required_without:subjectEs'],
            'subjectEs' => ['nullable', 'string', 'max:255', 'required_without:subjectEu'],
            'bodyEu' => ['nullable', 'string', 'required_without:bodyEs'],
            'bodyEs' => ['nullable', 'string', 'required_without:bodyEu'],
            'channel' => ['required', 'string', Rule::in(['email', 'sms', 'whatsapp', 'telegram'])],
        ];
    }

    private function templateName(): string
    {
        $subject = $this->normalizeNullableValue($this->subjectEu)
            ?? $this->normalizeNullableValue($this->subjectEs);

        if ($subject !== null) {
            return mb_substr($subject, 0, 255);
        }

        return __('campaigns.admin.template') . ' ' . now()->format('Y-m-d H:i');
    }

    private function normalizeNullableValue(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function storeAttachments(Campaign $campaign): void
    {
        foreach ($this->attachments as $attachment) {
            $path = $attachment->store('campaign-documents/' . $campaign->id, 'public');

            CampaignDocument::query()->create([
                'campaign_id' => $campaign->id,
                'filename' => $attachment->getClientOriginalName(),
                'path' => $path,
                'mime_type' => (string) $attachment->getClientMimeType(),
                'size_bytes' => (int) $attachment->getSize(),
                'is_public' => false,
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->subjectEu = '';
        $this->subjectEs = '';
        $this->bodyEu = '';
        $this->bodyEs = '';
        $this->channel = 'email';
        $this->selectedTemplateId = '';
        $this->scheduledAt = null;
        $this->attachments = [];
        $this->storedAttachments = [];
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;

        if ($this->currentUser()?->hasRole(Role::COMMUNITY_ADMIN)) {
            $this->recipientFilter = $this->options()->defaultRecipientFilter();
        } else {
            $this->recipientFilter = 'all';
        }

        $this->resetValidation();
        $this->recalculateRecipients();
    }

    private function canMutateCampaign(Campaign $campaign): bool
    {
        $user = $this->currentUser();

        if ($user === null || ! $user->can('update', $campaign)) {
            return false;
        }

        if (! $user->hasRole(Role::COMMUNITY_ADMIN)) {
            return true;
        }

        if (! str_contains((string) $campaign->recipient_filter, ':')) {
            return false;
        }

        [, $locationCode] = explode(':', (string) $campaign->recipient_filter, 2);

        return in_array($locationCode, $this->options()->allowedManagedLocationCodes(), true);
    }

    private function options(): CampaignAdminOptions
    {
        return CampaignAdminOptions::forUser($this->currentUser());
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function authorizeCreate(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('create', Campaign::class);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        return Auth::user();
    }
}
