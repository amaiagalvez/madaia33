<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use App\Models\Campaign;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\CampaignTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Concerns\BuildsLocaleFieldConfigs;

class AdminCampaignTemplateManager extends Component
{
    use BuildsLocaleFieldConfigs;
    use WithPagination;

    public ?int $editingId = null;

    public string $name = '';

    public string $subjectEu = '';

    public string $subjectEs = '';

    public string $bodyEu = '';

    public string $bodyEs = '';

    public string $channel = 'email';

    public bool $showForm = false;

    public ?int $confirmingDeleteId = null;

    public bool $showDeleteModal = false;

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $this->authorizeViewAny();

        return [
            'name' => ['required', 'string', 'max:255'],
            'subjectEu' => ['nullable', 'string', 'max:255', 'required_without:subjectEs'],
            'subjectEs' => ['nullable', 'string', 'max:255', 'required_without:subjectEu'],
            'bodyEu' => ['nullable', 'string', 'required_without:bodyEs'],
            'bodyEs' => ['nullable', 'string', 'required_without:bodyEu'],
            'channel' => ['required', 'string', Rule::in(['email', 'sms', 'whatsapp', 'telegram', 'manual'])],
        ];
    }

    public function createTemplate(): void
    {
        $this->authorizeViewAny();

        $this->resetForm();
        $this->showForm = true;
    }

    public function editTemplate(int $id): void
    {
        $this->authorizeViewAny();

        $template = CampaignTemplate::query()->findOrFail($id);

        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->subjectEu = (string) ($template->subject_eu ?? '');
        $this->subjectEs = (string) ($template->subject_es ?? '');
        $this->bodyEu = (string) ($template->body_eu ?? '');
        $this->bodyEs = (string) ($template->body_es ?? '');
        $this->channel = $template->channel;
        $this->showForm = true;
    }

    public function saveTemplate(): void
    {
        $this->validate();

        if ($this->editingId !== null) {
            $template = CampaignTemplate::query()->findOrFail($this->editingId);
            $template->update($this->payload());
        } else {
            $user = $this->currentUser();
            $locationId = null;
            if ($user?->hasRole(Role::COMMUNITY_ADMIN)) {
                $locationId = $user->managedLocations()->value('locations.id');
            }
            CampaignTemplate::query()->create([
                ...$this->payload(),
                'created_by_user_id' => $user?->id,
                'location_id' => $locationId,
            ]);
        }

        $this->resetForm();
        $this->showForm = false;

        session()->flash('message', __('general.messages.saved'));
    }

    public function confirmDelete(int $id): void
    {
        $this->authorizeViewAny();

        $this->confirmingDeleteId = $id;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->showDeleteModal = false;
    }

    public function deleteTemplate(): void
    {
        $this->authorizeViewAny();

        if ($this->confirmingDeleteId === null) {
            return;
        }

        CampaignTemplate::query()->findOrFail($this->confirmingDeleteId)->delete();

        $this->cancelDelete();

        session()->flash('message', __('general.messages.deleted'));
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function render(): View
    {
        $this->authorizeViewAny();

        return view('livewire.admin.campaign-template-manager', [
            'templates' => $this->templatesQuery()->latest()->paginate(12),
            'channelOptions' => [
                ['value' => 'email', 'label' => __('campaigns.admin.channels.email')],
                // ['value' => 'sms', 'label' => __('campaigns.admin.channels.sms')],
                ['value' => 'whatsapp', 'label' => __('campaigns.admin.channels.whatsapp')],
                ['value' => 'manual', 'label' => __('campaigns.admin.channels.manual')],
                // ['value' => 'telegram', 'label' => __('campaigns.admin.channels.telegram')],
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'name' => trim($this->name),
            'subject_eu' => $this->normalizeNullableValue($this->subjectEu),
            'subject_es' => $this->normalizeNullableValue($this->subjectEs),
            'body_eu' => $this->normalizeNullableValue($this->bodyEu),
            'body_es' => $this->normalizeNullableValue($this->bodyEs),
            'channel' => $this->channel,
        ];
    }

    private function normalizeNullableValue(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->subjectEu = '';
        $this->subjectEs = '';
        $this->bodyEu = '';
        $this->bodyEs = '';
        $this->channel = 'email';
        $this->resetValidation();
    }

    /**
     * @return Builder<CampaignTemplate>
     */
    private function templatesQuery(): Builder
    {
        $user = $this->currentUser();
        $query = CampaignTemplate::query();

        if ($user?->hasRole(Role::COMMUNITY_ADMIN)) {
            $managedLocationIds = $user->managedLocations()
                ->pluck('locations.id')
                ->all();

            if ($managedLocationIds === []) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('location_id', $managedLocationIds);
        }

        return $query;
    }

    private function authorizeViewAny(): void
    {
        $user = $this->currentUser();

        abort_if($user === null, 403);

        $this->authorize('viewAny', Campaign::class);
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
