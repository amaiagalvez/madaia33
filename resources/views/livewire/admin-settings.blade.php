<div x-data="{
    format(field, command) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            editor.focus();
            document.execCommand(command, false, null);
            this.sync(field);
        },
        link(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const url = window.prompt('{{ __('admin.settings_form.editor_link_prompt') }}', 'https://');
            if (!url) {
                return;
            }

            editor.focus();
            document.execCommand('createLink', false, url);
            this.sync(field);
        },
        sync(field) {
            const editor = this.$refs[field];
            if (!editor) {
                return;
            }

            const html = editor.innerHTML.trim();
            this.$wire.set(field, html === '<br>' ? '' : html);
        },
}">
    {{-- Section tabs --}}
    <div class="mb-6 rounded-lg border border-gray-200 bg-white p-2 shadow-sm">
        <nav class="flex flex-wrap gap-2" aria-label="{{ __('admin.settings') }}">
            @foreach ($availableSections as $section)
                <button type="button" wire:click="setSection('{{ $section }}')"
                    data-settings-section="{{ $section }}"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeSection === $section ? 'bg-[#edd2c7] text-[#793d3d]' : 'text-stone-600 hover:bg-[#edd2c7]/45 hover:text-[#793d3d]' }}"
                    aria-selected="{{ $activeSection === $section ? 'true' : 'false' }}"
                    aria-current="{{ $activeSection === $section ? 'page' : 'false' }}">
                    {{ __('admin.settings_sections.' . $section) }}
                </button>
            @endforeach
        </nav>
    </div>

    @if ($saved)
        <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-800">
            {{ __('general.messages.saved') }}
        </div>
    @endif

    <form wire:submit="save"
        class="space-y-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @if ($activeSection === 'contact_form')
            @include('livewire.admin-settings.partials.contact-form-tab')
        @elseif ($activeSection === 'front')
            @include('livewire.admin-settings.partials.front-tab')
        @elseif ($activeSection === 'recaptcha')
            @include('livewire.admin-settings.partials.recaptcha-tab')
        @endif

        <div>
            <button type="submit"
                class="rounded-md bg-[#d9755b] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#793d3d] focus:outline-none focus:ring-2 focus:ring-[#d9755b] focus:ring-offset-2">
                {{ __('general.buttons.save') }}
            </button>
        </div>
    </form>
</div>
