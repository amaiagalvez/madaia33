@props(['title', 'euField', 'esField', 'euLabel', 'esLabel', 'euValue' => '', 'esValue' => ''])

<div x-data="{ tab: 'eu' }" class="space-y-6 rounded-lg border border-gray-200 bg-stone-50 p-4"
    data-bilingual-field="{{ $euField }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm font-semibold text-stone-800">{{ $title }}</p>
        <nav class="flex w-fit gap-1 rounded-md border border-gray-200 bg-white p-1"
            aria-label="{{ __('admin.settings_form.language_tabs') }}">
            <button type="button" @click="tab = 'eu'" data-bilingual-tab="eu"
                :class="tab === 'eu' ? 'bg-amber-100 text-amber-900' :
                    'text-stone-600 hover:bg-amber-50 hover:text-stone-900'"
                class="rounded-md px-3 py-1.5 text-xs font-semibold transition-colors"
                :aria-selected="tab === 'eu' ? 'true' : 'false'">
                {{ __('admin.settings_form.language_tab_eus') }}
            </button>
            <button type="button" @click="tab = 'es'" data-bilingual-tab="es"
                :class="tab === 'es' ? 'bg-amber-100 text-amber-900' :
                    'text-stone-600 hover:bg-amber-50 hover:text-stone-900'"
                class="rounded-md px-3 py-1.5 text-xs font-semibold transition-colors"
                :aria-selected="tab === 'es' ? 'true' : 'false'">
                {{ __('admin.settings_form.language_tab_cas') }}
            </button>
        </nav>
    </div>

    <template x-if="tab === 'eu'">
        <div x-cloak data-bilingual-pane="eu"
            class="grid h-64 grid-rows-[1fr_auto] gap-1 overflow-hidden">
            <p id="{{ $euField }}Label" class="sr-only">{{ $euLabel }}</p>
            <div class="rounded-md border border-gray-300 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2"
                    role="toolbar" aria-label="{{ __('admin.settings_form.editor_toolbar') }}">
                    <button type="button" @click="format('{{ $euField }}', 'bold')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_bold') }}</button>
                    <button type="button" @click="format('{{ $euField }}', 'italic')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_italic') }}</button>
                    <button type="button" @click="format('{{ $euField }}', 'underline')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_underline') }}</button>
                    <button type="button" @click="link('{{ $euField }}')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_link') }}</button>
                </div>
                <div id="{{ $euField }}" x-ref="{{ $euField }}" contenteditable="true"
                    role="textbox" aria-labelledby="{{ $euField }}Label" aria-multiline="true"
                    @input="sync('{{ $euField }}')"
                    class="h-52 w-full overflow-y-auto bg-white px-3 py-2 text-sm text-stone-900 focus:outline-none">
                    {!! $euValue !!}</div>
            </div>
            <div class="min-h-5">
                @error($euField)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </template>

    <template x-if="tab === 'es'">
        <div x-cloak data-bilingual-pane="es"
            class="grid h-64 grid-rows-[1fr_auto] gap-1 overflow-hidden">
            <p id="{{ $esField }}Label" class="sr-only">{{ $esLabel }}</p>
            <div class="rounded-md border border-gray-300 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-1 border-b border-gray-200 px-2 py-2"
                    role="toolbar" aria-label="{{ __('admin.settings_form.editor_toolbar') }}">
                    <button type="button" @click="format('{{ $esField }}', 'bold')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_bold') }}</button>
                    <button type="button" @click="format('{{ $esField }}', 'italic')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_italic') }}</button>
                    <button type="button" @click="format('{{ $esField }}', 'underline')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_underline') }}</button>
                    <button type="button" @click="link('{{ $esField }}')"
                        class="rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-stone-700 transition-colors hover:bg-amber-50 hover:text-stone-900">{{ __('admin.settings_form.editor_link') }}</button>
                </div>
                <div id="{{ $esField }}" x-ref="{{ $esField }}" contenteditable="true"
                    role="textbox" aria-labelledby="{{ $esField }}Label" aria-multiline="true"
                    @input="sync('{{ $esField }}')"
                    class="h-52 w-full overflow-y-auto bg-white px-3 py-2 text-sm text-stone-900 focus:outline-none">
                    {!! $esValue !!}</div>
            </div>
            <div class="min-h-5">
                @error($esField)
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </template>
</div>
