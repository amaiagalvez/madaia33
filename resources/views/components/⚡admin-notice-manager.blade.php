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
    }

    public function saveNotice(): void
    {
        $this->validate([
            'titleEu' => 'required_without:titleEs|nullable|string|max:255',
            'titleEs' => 'required_without:titleEu|nullable|string|max:255',
            'contentEu' => 'nullable|string',
            'contentEs' => 'nullable|string',
        ]);

        $titleEu = filled($this->titleEu) ? $this->titleEu : null;
        $titleEs = filled($this->titleEs) ? $this->titleEs : null;

        if ($this->editingNoticeId) {
            $notice = Notice::findOrFail($this->editingNoticeId);
            $notice->update([
                'title_eu' => $titleEu,
                'title_es' => $titleEs,
                'content_eu' => filled($this->contentEu) ? $this->contentEu : null,
                'content_es' => filled($this->contentEs) ? $this->contentEs : null,
                'is_public' => $this->isPublic,
                'published_at' => $this->isPublic && !$notice->published_at ? now() : $notice->published_at,
            ]);
        } else {
            $base = $titleEu ?? ($titleEs ?? 'notice');
            $slug = Str::slug($base) . '-' . Str::random(6);

            $notice = Notice::create([
                'slug' => $slug,
                'title_eu' => $titleEu,
                'title_es' => $titleEs,
                'content_eu' => filled($this->contentEu) ? $this->contentEu : null,
                'content_es' => filled($this->contentEs) ? $this->contentEs : null,
                'is_public' => $this->isPublic,
                'published_at' => $this->isPublic ? now() : null,
            ]);
        }

        // Sync locations
        $notice->locations()->delete();
        foreach ($this->selectedLocations as $code) {
            NoticeLocation::create([
                'notice_id' => $notice->id,
                'location_type' => CommunityLocations::typeForCode($code),
                'location_code' => $code,
            ]);
        }

        $this->resetForm();
        unset($this->notices);
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

    // ── Private helpers ──────────────────────────────────────────────────────

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

<div>
    {{-- ── Form ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">
            {{ $editingNoticeId ? __('notices.admin.edit') : __('notices.admin.create') }}
        </h2>

        <form wire:submit="saveNotice" class="space-y-4">
            {{-- Bilingual titles --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="titleEu" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('notices.admin.title_eu') }}
                    </label>
                    <input id="titleEu" type="text" wire:model="titleEu"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500" />
                    @error('titleEu')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="titleEs" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('notices.admin.title_es') }}
                    </label>
                    <input id="titleEs" type="text" wire:model="titleEs"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500" />
                    @error('titleEs')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Bilingual content --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contentEu" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('notices.admin.content_eu') }}
                    </label>
                    <textarea id="contentEu" wire:model="contentEu" rows="5"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"></textarea>
                </div>
                <div>
                    <label for="contentEs" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ __('notices.admin.content_es') }}
                    </label>
                    <textarea id="contentEs" wire:model="contentEs" rows="5"
                        class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-1 focus:ring-gray-500"></textarea>
                </div>
            </div>

            {{-- Location selector --}}
            <div>
                <p class="block text-sm font-medium text-gray-700 mb-2">{{ __('notices.admin.locations') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\CommunityLocations::PORTALS as $portal)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" wire:model="selectedLocations" value="{{ $portal }}"
                                class="rounded border-gray-300 text-gray-700 focus:ring-gray-500" />
                            <span class="text-sm text-gray-700">{{ __('notices.portal') }} {{ $portal }}</span>
                        </label>
                    @endforeach
                    @foreach (\App\CommunityLocations::GARAGES as $garage)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" wire:model="selectedLocations" value="{{ $garage }}"
                                class="rounded border-gray-300 text-gray-700 focus:ring-gray-500" />
                            <span class="text-sm text-gray-700">{{ __('notices.garage') }} {{ $garage }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Publish toggle --}}
            <div class="flex items-center gap-2">
                <input id="isPublic" type="checkbox" wire:model="isPublic"
                    class="rounded border-gray-300 text-gray-700 focus:ring-gray-500" />
                <label for="isPublic" class="text-sm font-medium text-gray-700">
                    {{ __('notices.admin.is_public') }}
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    {{ $editingNoticeId ? __('notices.admin.edit') : __('notices.admin.create') }}
                </button>
                @if ($editingNoticeId)
                    <button type="button" wire:click="cancelEdit"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        {{ __('general.buttons.cancel') }}
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- ── Delete confirmation modal ───────────────────────────────────────── --}}
    @if ($confirmingDeleteId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" role="dialog" aria-modal="true">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
                <p class="text-sm text-gray-700 mb-6">{{ __('notices.admin.confirm_delete') }}</p>
                <div class="flex justify-end gap-3">
                    <button wire:click="cancelDelete"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('general.buttons.cancel') }}
                    </button>
                    <button wire:click="deleteNotice"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                        {{ __('general.buttons.delete') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Notices list ────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('notices.admin.list') }}</h2>
        </div>

        @if ($this->notices->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-500">{{ __('notices.empty') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('notices.admin.title_eu') }} / {{ __('notices.admin.title_es') }}
                            </th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('notices.admin.is_public') }}
                            </th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('notices.admin.locations') }}
                            </th>
                            <th class="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('notices.published_at') }}
                            </th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($this->notices as $notice)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $notice->title ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($notice->is_public)
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                            {{ __('notices.admin.publish') }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                            {{ __('notices.admin.unpublish') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($notice->locations as $loc)
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $loc->location_type === 'portal' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
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
                                    <div class="flex items-center gap-2 justify-end">
                                        <button wire:click="editNotice({{ $notice->id }})"
                                            class="text-xs text-gray-600 hover:text-gray-900 underline">
                                            {{ __('notices.admin.edit') }}
                                        </button>
                                        @if ($notice->is_public)
                                            <button wire:click="unpublishNotice({{ $notice->id }})"
                                                class="text-xs text-amber-600 hover:text-amber-800 underline">
                                                {{ __('notices.admin.unpublish') }}
                                            </button>
                                        @else
                                            <button wire:click="publishNotice({{ $notice->id }})"
                                                class="text-xs text-green-600 hover:text-green-800 underline">
                                                {{ __('notices.admin.publish') }}
                                            </button>
                                        @endif
                                        <button wire:click="confirmDelete({{ $notice->id }})"
                                            class="text-xs text-red-600 hover:text-red-800 underline">
                                            {{ __('general.buttons.delete') }}
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
