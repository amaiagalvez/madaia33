<?php

use App\CommunityLocations;
use App\Models\Notice;
use App\Models\NoticeLocation;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    // ── Form state ──────────────────────────────────────────────────────────
    public ?int $editingNoticeId = null;

    public string $titleEu = '';
    public string $titleEs = '';
    public string $contentEu = '';
    public string $contentEs = '';
    public bool $isPublic = false;

    /** @var string[] */
    public array $selectedLocations = [];

    // ── Delete confirmation ──────────────────────────────────────────────────
    public ?int $confirmingDeleteId = null;

    // ── Publish confirmation ─────────────────────────────────────────────────
    public ?int $confirmingPublishId = null;

    public string $publishAction = '';

    // ── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function notices(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notice::with('locations')->orderByDesc('created_at')->paginate(15);
    }

    // ── Form helpers ─────────────────────────────────────────────────────────

    public function createNotice(): void
    {
        $this->resetForm();
        $this->editingNoticeId = null;
    }

    public function editNotice(int $id): void
    {
        $notice = Notice::with('locations')->findOrFail($id);

        $this->editingNoticeId = $id;
        $this->titleEu = $notice->title_eu ?? '';
        $this->titleEs = $notice->title_es ?? '';
        $this->contentEu = $notice->content_eu ?? '';
        $this->contentEs = $notice->content_es ?? '';
        $this->isPublic = $notice->is_public;
        $this->selectedLocations = $notice->locations->pluck('location_code')->toArray();
        $this->dispatch('admin-notice-form-focus');
    }

    public function saveNotice(): void
    {
        $this->validate([
            'titleEu' => 'required_without:titleEs|nullable|string|max:255',
            'titleEs' => 'required_without:titleEu|nullable|string|max:255',
            'contentEu' => 'nullable|string',
            'contentEs' => 'nullable|string',
        ]);

        if ($this->editingNoticeId) {
            $notice = $this->updateNotice();
        } else {
            $notice = $this->createNewNotice();
        }

        $this->syncLocations($notice);

        $this->resetForm();
        unset($this->notices);
        session()->flash('message', __('general.messages.saved'));
        $this->dispatch('admin-notice-saved');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    // ── Publish / Unpublish ──────────────────────────────────────────────────

    public function publishNotice(int $id): void
    {
        $notice = Notice::findOrFail($id);
        $notice->update([
            'is_public' => true,
            'published_at' => $notice->published_at ?? now(),
        ]);
        unset($this->notices);
    }

    public function unpublishNotice(int $id): void
    {
        $notice = Notice::findOrFail($id);
        $notice->update(['is_public' => false]);
        unset($this->notices);
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteNotice(): void
    {
        if ($this->confirmingDeleteId) {
            Notice::findOrFail($this->confirmingDeleteId)->delete();
            $this->confirmingDeleteId = null;
            unset($this->notices);
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function confirmPublish(int $id, bool $publish): void
    {
        $this->confirmingPublishId = $id;
        $this->publishAction = $publish ? 'publish' : 'unpublish';
    }

    public function doPublish(): void
    {
        if (!$this->confirmingPublishId) {
            return;
        }

        if ($this->publishAction === 'publish') {
            $this->publishNotice($this->confirmingPublishId);
        } else {
            $this->unpublishNotice($this->confirmingPublishId);
        }

        $this->confirmingPublishId = null;
        $this->publishAction = '';
    }

    public function cancelPublish(): void
    {
        $this->confirmingPublishId = null;
        $this->publishAction = '';
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function updateNotice(): Notice
    {
        $notice = Notice::findOrFail($this->editingNoticeId);
        $notice->update([...$this->noticeAttributes(), 'published_at' => $this->isPublic && !$notice->published_at ? now() : $notice->published_at]);

        return $notice;
    }

    private function createNewNotice(): Notice
    {
        $base = $this->normalizedTitleEu() ?? ($this->normalizedTitleEs() ?? 'notice');

        return Notice::create([
            'slug' => Str::slug($base) . '-' . Str::random(6),
            ...$this->noticeAttributes(),
            'published_at' => $this->isPublic ? now() : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function noticeAttributes(): array
    {
        return [
            'title_eu' => $this->normalizedTitleEu(),
            'title_es' => $this->normalizedTitleEs(),
            'content_eu' => filled($this->contentEu) ? $this->contentEu : null,
            'content_es' => filled($this->contentEs) ? $this->contentEs : null,
            'is_public' => $this->isPublic,
        ];
    }

    private function normalizedTitleEu(): ?string
    {
        return filled($this->titleEu) ? $this->titleEu : null;
    }

    private function normalizedTitleEs(): ?string
    {
        return filled($this->titleEs) ? $this->titleEs : null;
    }

    private function syncLocations(Notice $notice): void
    {
        $notice->locations()->delete();

        foreach ($this->selectedLocations as $code) {
            NoticeLocation::create([
                'notice_id' => $notice->id,
                'location_type' => CommunityLocations::typeForCode($code),
                'location_code' => $code,
            ]);
        }
    }

    private function resetForm(): void
    {
        $this->editingNoticeId = null;
        $this->titleEu = '';
        $this->titleEs = '';
        $this->contentEu = '';
        $this->contentEs = '';
        $this->isPublic = false;
        $this->selectedLocations = [];
        $this->resetValidation();
    }
};
?>

<div x-data="{ focusForm() { const formCard = this.$root.querySelector('#admin-notice-form-card'); if (formCard) { formCard.scrollIntoView({ behavior: 'smooth', block: 'center' }); } const titleInput = this.$root.querySelector('#titleEu'); if (titleInput) { titleInput.focus(); } } }"
    x-on:admin-notice-form-focus.window="focusForm()">
    {{-- ── Form ──────────────────────────────────────────────────────────── --}}
    <div id="admin-notice-form-card" class="bg-white rounded-lg border border-gray-200 p-6 mb-8">

        <form wire:submit="saveNotice" class="space-y-5">
            {{-- Izenburua (EU/ES tabs) --}}
            <div x-data="{ titleTab: 'eu' }">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-stone-800">
                        {{ __('notices.admin.title') }}
                        <span class="text-red-500" aria-hidden="true">*</span>
                    </span>
                    <nav class="flex gap-1 rounded-md border border-gray-200 bg-white p-1"
                        aria-label="{{ __('admin.settings_form.language_tabs') }}">
                        <button type="button" @click="titleTab = 'eu'"
                            :class="titleTab === 'eu' ? 'bg-[#edd2c7] text-[#793d3d]' :
                                'text-stone-600 hover:bg-[#edd2c7]/45'"
                            class="rounded-md px-3 py-1 text-xs font-semibold transition-colors">EU</button>
                        <button type="button" @click="titleTab = 'es'"
                            :class="titleTab === 'es' ? 'bg-[#edd2c7] text-[#793d3d]' :
                                'text-stone-600 hover:bg-[#edd2c7]/45'"
                            class="rounded-md px-3 py-1 text-xs font-semibold transition-colors">ES</button>
                    </nav>
                </div>
                <div x-show="titleTab === 'eu'" x-cloak>
                    <input id="titleEu" type="text" wire:model="titleEu"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                    @error('titleEu')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div x-show="titleTab === 'es'" x-cloak>
                    <input id="titleEs" type="text" wire:model="titleEs"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]" />
                    @error('titleEs')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Edukia (EU/ES tabs) --}}
            <div x-data="{ contentTab: 'eu' }">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-stone-800">
                        {{ __('notices.admin.content') }}
                        <span class="text-red-500" aria-hidden="true">*</span>
                    </span>
                    <nav class="flex gap-1 rounded-md border border-gray-200 bg-white p-1"
                        aria-label="{{ __('admin.settings_form.language_tabs') }}">
                        <button type="button" @click="contentTab = 'eu'"
                            :class="contentTab === 'eu' ? 'bg-[#edd2c7] text-[#793d3d]' :
                                'text-stone-600 hover:bg-[#edd2c7]/45'"
                            class="rounded-md px-3 py-1 text-xs font-semibold transition-colors">EU</button>
                        <button type="button" @click="contentTab = 'es'"
                            :class="contentTab === 'es' ? 'bg-[#edd2c7] text-[#793d3d]' :
                                'text-stone-600 hover:bg-[#edd2c7]/45'"
                            class="rounded-md px-3 py-1 text-xs font-semibold transition-colors">ES</button>
                    </nav>
                </div>
                <div x-show="contentTab === 'eu'" x-cloak>
                    <textarea id="contentEu" wire:model="contentEu" rows="5"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"></textarea>
                </div>
                <div x-show="contentTab === 'es'" x-cloak>
                    <textarea id="contentEs" wire:model="contentEs" rows="5"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-[#d9755b] focus:outline-none focus:ring-1 focus:ring-[#d9755b]"></textarea>
                </div>
            </div>

            {{-- Kokapena(k) --}}
            <div>
                <p class="text-sm font-semibold text-stone-800 mb-2">
                    {{ __('notices.admin.locations') }}
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\CommunityLocations::PORTALS as $portal)
                    <label wire:key="portal-{{ $portal }}"
                        class="cursor-pointer select-none">
                        <input type="checkbox" wire:model="selectedLocations"
                            value="{{ $portal }}" class="sr-only peer" />
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors
                                border-gray-300 bg-white text-gray-600
                                peer-checked:bg-[#d9755b] peer-checked:text-white peer-checked:border-[#d9755b]
                                hover:border-[#d9755b] hover:bg-[#edd2c7]/40 hover:text-[#793d3d]
                                peer-checked:hover:border-[#d9755b] peer-checked:hover:bg-[#d9755b] peer-checked:hover:text-white">
                            {{ __('notices.portal') }} {{ $portal }}
                        </span>
                    </label>
                    @endforeach
                    @foreach (\App\CommunityLocations::GARAGES as $garage)
                    <label wire:key="garage-{{ $garage }}"
                        class="cursor-pointer select-none">
                        <input type="checkbox" wire:model="selectedLocations"
                            value="{{ $garage }}" class="sr-only peer" />
                        <span
                            class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors
                                border-gray-300 bg-white text-gray-600
                                peer-checked:bg-stone-600 peer-checked:text-white peer-checked:border-stone-600
                                hover:border-stone-400 hover:bg-stone-100 hover:text-stone-800
                                peer-checked:hover:border-stone-600 peer-checked:hover:bg-stone-600 peer-checked:hover:text-white">
                            {{ __('notices.garage') }} {{ $garage }}
                        </span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Publikatu toggle --}}
            <div>
                <label for="isPublic" class="mb-2 block text-sm font-semibold text-stone-800">
                    {{ __('notices.admin.is_public') }}
                </label>
                <label for="isPublic"
                    class="flex cursor-pointer items-center justify-between rounded-2xl border border-brand-300/50 bg-brand-100/30 px-4 py-3 transition-colors hover:border-brand-600/50 hover:bg-brand-100/50">
                    <div>
                        <p class="text-sm font-semibold text-brand-900">
                            {{ __('notices.admin.is_public') }}
                        </p>
                        <p class="text-xs text-stone-600">
                            {{ $isPublic ? __('notices.admin.publish') : __('notices.admin.unpublish') }}
                        </p>
                    </div>
                    <span
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors {{ $isPublic ? 'bg-brand-600' : 'bg-stone-300' }}">
                        <span
                            class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform {{ $isPublic ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </span>
                    <input id="isPublic" type="checkbox" wire:model.live="isPublic"
                        class="sr-only" />
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    {{ $editingNoticeId ? __('general.buttons.save') : __('general.buttons.create_new') }}
                </button>
                @if ($editingNoticeId)
                <button type="button" wire:click="cancelEdit"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                    {{ __('general.buttons.cancel') }}
                </button>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Delete confirmation modal ───────────────────────────────────────── --}}
    @if ($confirmingDeleteId)
    <dialog open class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
        aria-labelledby="delete-modal-title">
        <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 space-y-4">
            <div class="flex items-start gap-3">
                <div
                    class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-red-100">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </div>
                <div>
                    <h3 id="delete-modal-title" class="text-base font-semibold text-gray-900">
                        {{ __('notices.admin.delete_title') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('notices.admin.confirm_delete') }}
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="cancelDelete"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                    {{ __('general.buttons.cancel') }}
                </button>
                <button type="button" wire:click="deleteNotice"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    {{ __('general.buttons.delete') }}
                </button>
            </div>
        </div>
    </dialog>
    @endif

    {{-- ── Publish confirmation modal ──────────────────────────────────────── --}}
    @if ($confirmingPublishId)
    <dialog open class="fixed inset-0 z-50 m-0 grid h-full w-full place-items-center bg-transparent p-4"
        aria-labelledby="publish-modal-title">
        <div class="bg-white rounded-xl shadow-2xl p-6 max-w-sm w-full mx-4 space-y-4">
            <div class="flex items-start gap-3">
                <div
                    class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full
                        {{ $publishAction === 'publish' ? 'bg-green-100' : 'bg-amber-100' }}">
                    @if ($publishAction === 'publish')
                    <svg class="w-5 h-5 text-green-600" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-amber-600" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                    @endif
                </div>
                <div>
                    <h3 id="publish-modal-title"
                        class="text-base font-semibold text-gray-900">
                        {{ $publishAction === 'publish' ? __('notices.admin.publish') : __('notices.admin.unpublish') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ $publishAction === 'publish' ? __('notices.admin.confirm_publish') : __('notices.admin.confirm_unpublish') }}
                    </p>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" wire:click="cancelPublish"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#d9755b]">
                    {{ __('general.buttons.cancel') }}
                </button>
                <button type="button" wire:click="doPublish"
                    class="rounded-md px-4 py-2 text-sm font-medium text-white focus:outline-none focus:ring-2
                            {{ $publishAction === 'publish' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400' }}">
                    {{ __('general.buttons.confirm') }}
                </button>
            </div>
        </div>
    </dialog>
    @endif

    {{-- ── Notices list ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">

        @if ($this->notices->isEmpty())
        <div class="px-6 py-12 text-center">
            <p class="text-sm text-gray-500">{{ __('notices.empty') }}</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th
                            class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('notices.admin.title_eu') }} /
                            {{ __('notices.admin.title_es') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('notices.admin.published_status') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('notices.admin.locations') }}
                        </th>
                        <th
                            class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('notices.published_at') }}
                        </th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($this->notices as $notice)
                    <tr wire:key="notice-row-{{ $notice->id }}"
                        class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $notice->title ?: '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <button type="button"
                                wire:click="confirmPublish({{ $notice->id }}, {{ $notice->is_public ? 'false' : 'true' }})"
                                title="{{ $notice->is_public ? __('notices.admin.unpublish') : __('notices.admin.publish') }}"
                                class="inline-flex min-w-28 items-center justify-center gap-2 rounded-full border px-3 py-1.5 text-xs font-semibold transition-colors {{ $notice->is_public ? 'border-green-200 bg-green-50 text-green-700 hover:border-green-300 hover:bg-green-100' : 'border-red-200 bg-red-50 text-red-600 hover:border-red-300 hover:bg-red-100' }}">
                                @if ($notice->is_public)
                                <svg class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                @else
                                <svg class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor">
                                    <path stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6 18 18 6M6 6l12 12" />
                                </svg>
                                @endif
                                <span>{{ $notice->is_public ? __('notices.admin.published_status') : __('notices.admin.is_public') }}</span>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($notice->locations as $loc)
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $loc->location_type === 'portal' ? 'bg-[#d9755b]/15 text-[#793d3d]' : 'bg-brand-300/30 text-stone-700' }}">
                                    {{ $loc->location_code }}
                                </span>
                                @empty
                                <span class="text-gray-400 text-xs">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $notice->published_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 justify-end">
                                {{-- Edit --}}
                                <button type="button"
                                    wire:click="editNotice({{ $notice->id }})"
                                    title="{{ __('general.buttons.edit') }}"
                                    class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-brand-300/40 hover:bg-brand-100/40 hover:text-[#d9755b]">
                                    <svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                {{-- Delete --}}
                                <button type="button"
                                    wire:click="confirmDelete({{ $notice->id }})"
                                    title="{{ __('general.buttons.delete') }}"
                                    class="rounded-full border border-transparent p-2 text-gray-400 transition-colors hover:border-red-200 hover:bg-red-50 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none"
                                        viewBox="0 0 24 24" stroke-width="1.5"
                                        stroke="currentColor">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $this->notices->links() }}
        </div>
        @endif
    </div>
</div>