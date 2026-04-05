<div>
    {{-- Flash message --}}
    @if (session()->has('message'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ session('message') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-700">{{ __('notices.admin.list') }}</h2>
        @unless ($showForm)
            <button wire:click="createNotice"
                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('general.buttons.create') }}
            </button>
        @endunless
    </div>

    {{-- Inline form --}}
    @if ($showForm)
        <div class="mb-8 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-base font-semibold text-gray-800">
                {{ $editingId ? __('notices.admin.edit') : __('notices.admin.create') }}
            </h3>

            <form wire:submit="saveNotice" novalidate>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Title EU --}}
                    <div>
                        <label for="titleEu" class="block text-sm font-medium text-gray-700">
                            {{ __('notices.admin.title_eu') }} <span class="text-red-500">*</span>
                        </label>
                        <input id="titleEu" type="text" wire:model="titleEu"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('titleEu') border-red-500 @enderror" />
                        @error('titleEu')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Title ES --}}
                    <div>
                        <label for="titleEs" class="block text-sm font-medium text-gray-700">
                            {{ __('notices.admin.title_es') }}
                        </label>
                        <input id="titleEs" type="text" wire:model="titleEs"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>

                    {{-- Content EU --}}
                    <div class="sm:col-span-2">
                        <label for="contentEu" class="block text-sm font-medium text-gray-700">
                            {{ __('notices.admin.content_eu') }} <span class="text-red-500">*</span>
                        </label>
                        <textarea id="contentEu" wire:model="contentEu" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('contentEu') border-red-500 @enderror"></textarea>
                        @error('contentEu')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Content ES --}}
                    <div class="sm:col-span-2">
                        <label for="contentEs" class="block text-sm font-medium text-gray-700">
                            {{ __('notices.admin.content_es') }}
                        </label>
                        <textarea id="contentEs" wire:model="contentEs" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    {{-- Locations --}}
                    <div class="sm:col-span-2">
                        <fieldset>
                            <legend class="block text-sm font-medium text-gray-700">
                                {{ __('notices.admin.locations') }}
                            </legend>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($allLocations as $loc)
                                    <label
                                        class="inline-flex cursor-pointer items-center gap-1 rounded-full border border-gray-300 px-3 py-1 text-xs font-medium hover:bg-gray-50">
                                        <input type="checkbox" wire:model="selectedLocations"
                                            value="{{ $loc['code'] }}"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                        {{ $loc['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>

                    {{-- Is public toggle --}}
                    <div class="flex items-center gap-3">
                        <label for="isPublic" class="text-sm font-medium text-gray-700">
                            {{ __('notices.admin.is_public') }}
                        </label>
                        <input id="isPublic" type="checkbox" wire:model="isPublic"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('general.buttons.save') }}
                    </button>
                    <button type="button" wire:click="cancelForm"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('general.buttons.cancel') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Notices table --}}
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ __('notices.admin.title_eu') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ __('notices.admin.locations') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ __('notices.admin.is_public') }}
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                        {{ __('notices.published_at') }}
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">{{ __('general.buttons.edit') }}</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse ($notices as $notice)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $notice->title_eu }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if ($notice->locations->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($notice->locations as $loc)
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">
                                            {{ $loc->location_code }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
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
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $notice->published_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Edit --}}
                                <button wire:click="editNotice({{ $notice->id }})"
                                    class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('general.buttons.edit') }}
                                </button>

                                {{-- Publish / Unpublish --}}
                                @if ($notice->is_public)
                                    <button wire:click="unpublishNotice({{ $notice->id }})"
                                        class="text-yellow-600 hover:text-yellow-900">
                                        {{ __('notices.admin.unpublish') }}
                                    </button>
                                @else
                                    <button wire:click="publishNotice({{ $notice->id }})"
                                        class="text-green-600 hover:text-green-900">
                                        {{ __('notices.admin.publish') }}
                                    </button>
                                @endif

                                {{-- Delete --}}
                                @if ($confirmingDeleteId === $notice->id)
                                    <span class="text-sm text-gray-600">{{ __('notices.admin.confirm_delete') }}</span>
                                    <button wire:click="deleteNotice" class="text-red-600 hover:text-red-900">
                                        {{ __('general.buttons.delete') }}
                                    </button>
                                    <button wire:click="cancelDelete" class="text-gray-500 hover:text-gray-700">
                                        {{ __('general.buttons.cancel') }}
                                    </button>
                                @else
                                    <button wire:click="confirmDelete({{ $notice->id }})"
                                        class="text-red-600 hover:text-red-900">
                                        {{ __('general.buttons.delete') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                            {{ __('notices.empty') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
