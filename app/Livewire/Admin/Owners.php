<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Owner;
use Livewire\Component;
use App\SupportedLocales;
use Livewire\WithPagination;
use App\Models\OwnerAuditLog;
use Illuminate\Contracts\View\View;
use App\Support\OwnerAuditFieldLabel;
use App\Services\CreateOwnerFormService;
use App\Validations\OwnerFormValidation;
use App\Actions\Owners\CreateOwnerAction;
use App\Concerns\InteractsWithAdminOwners;
use App\Actions\Properties\AssignPropertyAction;
use App\Actions\Properties\UnassignPropertyAction;
use App\Livewire\Admin\Concerns\ManagesOwnerAssignments;

class Owners extends Component
{
    use InteractsWithAdminOwners;
    use ManagesOwnerAssignments;
    use WithPagination;

    private CreateOwnerAction $createOwnerAction;

    private CreateOwnerFormService $createOwnerFormService;

    private AssignPropertyAction $assignPropertyAction;

    private UnassignPropertyAction $unassignPropertyAction;

    public bool $showCreateForm = false;

    public string $ownerId = '';

    public string $coprop1Name = '';

    public string $coprop1Surname = '';

    public string $coprop1Dni = '';

    public string $coprop1Phone = '';

    public bool $coprop1HasWhatsapp = false;

    public string $coprop1Email = '';

    public string $language = SupportedLocales::BASQUE;

    public string $coprop2Name = '';

    public string $coprop2Surname = '';

    public string $coprop2Dni = '';

    public string $coprop2Phone = '';

    public bool $coprop2HasWhatsapp = false;

    public string $coprop2Email = '';

    public string $filterStatus = 'active';

    public string $filterPortal = '';

    public string $filterLocal = '';

    public string $filterGarage = '';

    public string $filterStorage = '';

    public string $filterSearch = '';

    public string $ownershipView = 'default';

    // Edit owner slideover
    public bool $showEditOwnerForm = false;

    public ?int $editingOwnerId = null;

    public string $editCoprop1Name = '';

    public string $editCoprop1Surname = '';

    public string $editCoprop1Dni = '';

    public string $editCoprop1Phone = '';

    public bool $editCoprop1HasWhatsapp = false;

    public bool $editCoprop1PhoneInvalid = false;

    public bool $editCoprop1EmailInvalid = false;

    public string $editCoprop1Email = '';

    public string $editLanguage = SupportedLocales::BASQUE;

    public string $editCoprop2Name = '';

    public string $editCoprop2Surname = '';

    public string $editCoprop2Dni = '';

    public string $editCoprop2Phone = '';

    public bool $editCoprop2HasWhatsapp = false;

    public bool $editCoprop2PhoneInvalid = false;

    public bool $editCoprop2EmailInvalid = false;

    public string $editCoprop2Email = '';

    public int $editOwnerAuditLogCount = 0;

    /**
     * @var array<int, array{field_label: string, old_value: string, new_value: string, changed_by: string, changed_at: string}>
     */
    public array $editOwnerAuditLogs = [];

    /**
     * @var array<int, array{property_id: string, start_date: string, end_date: string}>
     */
    public array $newAssignments = [];

    public ?int $expandedOwnerId = null;

    /**
     * @var array<int, array{start_date: string, end_date: string, admin_validated: bool, owner_validated: bool}>
     */
    public array $assignmentEdits = [];

    public string $inlinePropertyId = '';

    public string $inlineStartDate = '';

    public string $inlineEndDate = '';

    public string $rowErrorMessage = '';

    public ?int $confirmingWelcomeOwnerId = null;

    public bool $showWelcomeModal = false;

    public string $warningMessage = '';

    public function boot(
        CreateOwnerAction $createOwnerAction,
        CreateOwnerFormService $createOwnerFormService,
        AssignPropertyAction $assignPropertyAction,
        UnassignPropertyAction $unassignPropertyAction,
    ): void {
        $this->createOwnerAction = $createOwnerAction;
        $this->createOwnerFormService = $createOwnerFormService;
        $this->assignPropertyAction = $assignPropertyAction;
        $this->unassignPropertyAction = $unassignPropertyAction;
    }

    /**
     * Reset pagination when any filter changes
     * Handling filterStatus separately to also reset ownershipView
     */
    public function updatedFilterStatus(): void
    {
        $this->resetPage();
        $this->ownershipView = 'default';
    }

    public function updatedFilterPortal(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLocal(): void
    {
        $this->resetPage();
    }

    public function updatedFilterGarage(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStorage(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSearch(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->newAssignments = [$this->newAssignmentRow()];

        $editOwnerId = (int) request()->integer('editOwner');

        if ($editOwnerId > 0) {
            $this->openEditOwnerForm($editOwnerId);
        }
    }

    /**
     * @return array{property_id: string, start_date: string, end_date: string}
     */
    private function newAssignmentRow(): array
    {
        return [
            'property_id' => '',
            'start_date' => '',
            'end_date' => '',
        ];
    }

    public function showWithoutProperties(): void
    {
        $this->filterStatus = 'inactive';
        $this->ownershipView = 'without_properties';
        $this->resetPage();
    }

    public function clearWithoutProperties(): void
    {
        $this->ownershipView = 'default';
        $this->filterStatus = 'all';
        $this->resetPage();
    }

    public function addAssignmentRow(): void
    {
        $this->newAssignments[] = $this->newAssignmentRow();
    }

    public function removeAssignmentRow(int $index): void
    {
        if (count($this->newAssignments) === 1) {
            $this->newAssignments = [$this->newAssignmentRow()];

            return;
        }

        unset($this->newAssignments[$index]);
        $this->newAssignments = array_values($this->newAssignments);
    }

    public function createOwner(): void
    {
        $this->warningMessage = '';

        $data = $this->validate(
            $this->ownerCreationRules(),
            $this->ownerCreationMessages(),
            $this->ownerCreationAttributes(),
        );

        $dateErrors = $this->createOwnerFormService->validateAssignmentDates($data['newAssignments']);

        if ($dateErrors !== []) {
            foreach ($dateErrors as $field => $message) {
                $this->addError($field, $message);
            }

            return;
        }

        $owner = $this->createOwnerAction->execute($this->createOwnerFormService->prepareOwnerData($data));

        if (! $owner->welcome) {
            $this->flashOwnerWelcomeNoEmailWarning();
        }

        $this->resetCreateOwnerFormState();
    }

    public function confirmResendWelcomeMail(int $ownerId): void
    {
        $this->confirmingWelcomeOwnerId = $ownerId;
        $this->showWelcomeModal = true;
    }

    public function doResendWelcomeMail(): void
    {
        if ($this->confirmingWelcomeOwnerId === null) {
            return;
        }

        $this->warningMessage = '';

        $this->resendOwnerWelcomeMail($this->confirmingWelcomeOwnerId);
        $this->cancelResendWelcomeMail();
    }

    public function cancelResendWelcomeMail(): void
    {
        $this->confirmingWelcomeOwnerId = null;
        $this->showWelcomeModal = false;
    }

    public function resendOwnerWelcomeMail(int $ownerId): void
    {
        $owner = Owner::query()
            ->with(['user', 'assignments.property.location'])
            ->findOrFail($ownerId);

        $sent = $this->createOwnerAction->sendWelcomeMailToOwner($owner);

        if (! $sent) {
            $this->flashOwnerWelcomeNoEmailWarning();
        }
    }

    private function flashOwnerWelcomeNoEmailWarning(): void
    {
        $this->warningMessage = __('admin.owners.welcome_not_sent_missing_email');
        session()->flash('warning', $this->warningMessage);
    }

    public function openEditOwnerForm(int $ownerId): void
    {
        $owner = Owner::findOrFail($ownerId);

        $this->editingOwnerId = $ownerId;
        $this->editCoprop1Name = $owner->coprop1_name;
        $this->editCoprop1Surname = $owner->coprop1_surname ?? '';
        $this->editCoprop1Dni = $owner->coprop1_dni ?? '';
        $this->editCoprop1Phone = $owner->coprop1_phone ?? '';
        $this->editCoprop1HasWhatsapp = (bool) $owner->coprop1_has_whatsapp;
        $this->editCoprop1PhoneInvalid = (bool) $owner->coprop1_phone_invalid;
        $this->editCoprop1EmailInvalid = (bool) $owner->coprop1_email_invalid;
        $this->editCoprop1Email = $owner->coprop1_email;
        $this->editLanguage = $owner->language ?? SupportedLocales::BASQUE;
        $this->editCoprop2Name = $owner->coprop2_name ?? '';
        $this->editCoprop2Surname = $owner->coprop2_surname ?? '';
        $this->editCoprop2Dni = $owner->coprop2_dni ?? '';
        $this->editCoprop2Phone = $owner->coprop2_phone ?? '';
        $this->editCoprop2HasWhatsapp = (bool) $owner->coprop2_has_whatsapp;
        $this->editCoprop2PhoneInvalid = (bool) $owner->coprop2_phone_invalid;
        $this->editCoprop2EmailInvalid = (bool) $owner->coprop2_email_invalid;
        $this->editCoprop2Email = $owner->coprop2_email ?? '';
        $this->loadEditOwnerAuditLogs($owner);
        $this->resetValidation();
        $this->showEditOwnerForm = true;
    }

    public function saveEditOwner(): void
    {
        $owner = Owner::findOrFail((int) $this->editingOwnerId);

        $this->validate(
            OwnerFormValidation::adminEditRules($owner->user_id),
            [],
            OwnerFormValidation::adminEditAttributes(),
        );

        $owner->update([
            ...$this->editOwnerPrimaryFields(),
            ...$this->editOwnerSecondaryFields(),
        ]);

        $this->cancelEditOwner();
    }

    /**
     * @return array<string, mixed>
     */
    private function editOwnerPrimaryFields(): array
    {
        return [
            'coprop1_name' => $this->editCoprop1Name,
            'coprop1_surname' => $this->editCoprop1Surname ?: null,
            'coprop1_dni' => $this->editCoprop1Dni ?: null,
            'coprop1_phone' => $this->editCoprop1Phone ?: null,
            'coprop1_has_whatsapp' => $this->editCoprop1HasWhatsapp,
            'coprop1_email' => $this->editCoprop1Email,
            'language' => $this->editLanguage,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function editOwnerSecondaryFields(): array
    {
        return [
            'coprop2_name' => $this->editCoprop2Name ?: null,
            'coprop2_surname' => $this->editCoprop2Surname ?: null,
            'coprop2_dni' => $this->editCoprop2Dni ?: null,
            'coprop2_phone' => $this->editCoprop2Phone ?: null,
            'coprop2_has_whatsapp' => $this->editCoprop2HasWhatsapp,
            'coprop2_email' => $this->editCoprop2Email ?: null,
        ];
    }

    public function cancelEditOwner(): void
    {
        $this->showEditOwnerForm = false;
        $this->editingOwnerId = null;
        $this->reset([
            'editCoprop1Name',
            'editCoprop1Surname',
            'editCoprop1Dni',
            'editCoprop1Phone',
            'editCoprop1HasWhatsapp',
            'editCoprop1PhoneInvalid',
            'editCoprop1EmailInvalid',
            'editCoprop1Email',
            'editLanguage',
            'editCoprop2Name',
            'editCoprop2Surname',
            'editCoprop2Dni',
            'editCoprop2Phone',
            'editCoprop2HasWhatsapp',
            'editCoprop2PhoneInvalid',
            'editCoprop2EmailInvalid',
            'editCoprop2Email',
            'editOwnerAuditLogCount',
            'editOwnerAuditLogs',
        ]);
        $this->resetValidation();
    }

    private function loadEditOwnerAuditLogs(Owner $owner): void
    {
        $logsQuery = $owner->auditLogs()->with('changedBy:id,name')->latest();

        $this->editOwnerAuditLogCount = (clone $logsQuery)->count();

        $this->editOwnerAuditLogs = $logsQuery
            ->limit(25)
            ->get()
            ->map(function (OwnerAuditLog $log): array {
                $changedByUser = $log->changedBy;

                return [
                    'field_label' => OwnerAuditFieldLabel::for($log->field),
                    'old_value' => $log->old_value !== '' ? $log->old_value : '—',
                    'new_value' => $log->new_value !== '' ? $log->new_value : '—',
                    'changed_by' => $changedByUser instanceof User
                        ? $changedByUser->name
                        : __('admin.owners.audit.system'),
                    'changed_at' => $log->created_at?->format('d/m/Y H:i') ?? '—',
                ];
            })
            ->values()
            ->all();
    }

    public function cancelCreateOwner(): void
    {
        $this->reset([
            'ownerId',
            'coprop1Name',
            'coprop1Surname',
            'coprop1Dni',
            'coprop1Phone',
            'coprop1HasWhatsapp',
            'coprop1Email',
            'coprop2Name',
            'coprop2Surname',
            'coprop2Dni',
            'coprop2Phone',
            'coprop2HasWhatsapp',
            'coprop2Email',
        ]);

        $this->newAssignments = [$this->newAssignmentRow()];

        $this->resetValidation();
        $this->showCreateForm = false;
    }

    public function render(): View
    {
        return view('livewire.admin.owners.index', [
            'owners' => $this->buildOwnersQuery()->orderBy('coprop1_name')->paginate(20),
            ...$this->loadViewData(),
            'expandedAssignments' => $this->loadExpandedAssignments(),
        ]);
    }
}
