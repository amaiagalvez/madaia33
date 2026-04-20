<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Construction;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class AdminConstructionManager extends Component
{
    use WithPagination;

    public bool $showForm = false;

    public ?int $editingConstructionId = null;

    public string $title = '';

    public string $description = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public bool $isActive = true;

    /** @var array<int, string> */
    public array $selectedManagers = [];

    public ?int $confirmingDeleteId = null;

    public function mount(): void
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);
    }

    public function createConstruction(): void
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);

        $this->resetForm();
        $this->showForm = true;
    }

    public function editConstruction(int $constructionId): void
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);

        $construction = Construction::query()->with('managers')->findOrFail($constructionId);

        $this->editingConstructionId = $construction->id;
        $this->title = $construction->title;
        $this->description = (string) ($construction->description ?? '');
        $this->startsAt = (string) optional($construction->starts_at)->format('Y-m-d');
        $this->endsAt = (string) optional($construction->ends_at)->format('Y-m-d');
        $this->isActive = (bool) $construction->is_active;
        $this->selectedManagers = $construction->managers
            ->pluck('id')
            ->map(static fn (int $id): string => (string) $id)
            ->values()
            ->all();

        if (! $this->canAssignManagers()) {
            $this->selectedManagers = [];
        }

        $this->showForm = true;
    }

    public function saveConstruction(): void
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);

        $validated = $this->validate($this->rules());

        $construction = $this->editingConstructionId !== null
            ? Construction::query()->findOrFail($this->editingConstructionId)
            : new Construction;

        $construction->title = $validated['title'];
        $construction->description = $validated['description'] !== '' ? $validated['description'] : null;
        $construction->starts_at = $validated['startsAt'];
        $construction->ends_at = $validated['endsAt'] !== '' ? $validated['endsAt'] : null;
        $construction->is_active = (bool) $validated['isActive'];
        $construction->slug = Str::slug($construction->title);

        $construction->save();

        if ($this->canAssignManagers()) {
            $managerIds = User::query()
                ->whereIn('id', $this->selectedManagers)
                ->whereHas('roles', fn ($query) => $query->where('name', Role::CONSTRUCTION_MANAGER))
                ->pluck('id')
                ->all();

            $construction->managers()->sync($managerIds);
        }

        $this->resetForm();
        $this->showForm = false;
        session()->flash('message', __('general.messages.saved'));
    }

    public function toggleActive(int $constructionId): void
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);

        $construction = Construction::query()->findOrFail($constructionId);

        $construction->update([
            'is_active' => ! $construction->is_active,
        ]);
    }

    public function confirmDelete(int $constructionId): void
    {
        abort_unless($this->canDeleteConstruction(), 403);

        $this->confirmingDeleteId = $constructionId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function deleteConstruction(): void
    {
        abort_unless($this->canDeleteConstruction(), 403);

        if ($this->confirmingDeleteId === null) {
            return;
        }

        Construction::query()->findOrFail($this->confirmingDeleteId)->delete();

        $this->confirmingDeleteId = null;

        if ($this->editingConstructionId !== null) {
            $this->resetForm();
            $this->showForm = false;
        }

        session()->flash('message', __('general.messages.deleted'));
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function render(): View
    {
        abort_unless($this->currentUser()?->canManageConstructions(), 403);

        $constructions = Construction::query()
            ->withCount('managers')
            ->orderByDesc('starts_at')
            ->paginate(12);

        $managerOptions = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', Role::CONSTRUCTION_MANAGER))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.construction-manager', [
            'constructions' => $constructions,
            'managerOptions' => $managerOptions,
            'canAssignManagers' => $this->canAssignManagers(),
            'canDeleteConstruction' => $this->canDeleteConstruction(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $slug = Str::slug((string) $value);

                    if ($slug === '') {
                        $fail(__('admin.constructions.validation.slug_invalid'));

                        return;
                    }

                    $query = Construction::query()->where('slug', $slug);

                    if ($this->editingConstructionId !== null) {
                        $query->whereKeyNot($this->editingConstructionId);
                    }

                    if ($query->exists()) {
                        $fail(__('admin.constructions.validation.slug_unique'));
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'startsAt' => ['required', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'isActive' => ['boolean'],
            'selectedManagers' => ['array'],
            'selectedManagers.*' => [
                'string',
                Rule::exists('users', 'id'),
            ],
        ];
    }

    private function resetForm(): void
    {
        $this->editingConstructionId = null;
        $this->title = '';
        $this->description = '';
        $this->startsAt = '';
        $this->endsAt = '';
        $this->isActive = true;
        $this->selectedManagers = [];
        $this->resetValidation();
    }

    private function canAssignManagers(): bool
    {
        $user = $this->currentUser();

        return $user?->hasAnyRole([Role::SUPER_ADMIN, Role::GENERAL_ADMIN]) ?? false;
    }

    private function canDeleteConstruction(): bool
    {
        return $this->canAssignManagers();
    }

    private function currentUser(): ?User
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user;
    }
}
